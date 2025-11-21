<?php

namespace App\Services;

use App\Models\Correlative;
use Illuminate\Support\Facades\Log;

class CorrelativeService
{
    protected $branchId;
    protected $prefix = 'PED-';

    public function __construct(int $branchId)
    {
        $this->branchId = $branchId;
    }

    private function getNextCounter(int $counter): int
    {
        return $counter + 1;
    }

    private function formatCorrelative(int $correlative): string
    {
        // 6 dígitos con ceros a la izquierda
        return sprintf("%06d", $correlative);
    }

    /**
     * Obtiene y actualiza el correlativo para la sucursal,
     * pero NO hace commit. Usa lockForUpdate.
     * @return string|bool Retorna el correlativo si es exitoso, o false si falla.
     */
    public function getCorrelative()
    {
        // 1. Obtener el registro correlativo para la sucursal (CON BLOQUEO)
        $correlativeRecord = Correlative::where('branch_id', $this->branchId)
            ->lockForUpdate() // <--- Bloquea la fila. Requiere una transacción externa.
            ->first();

        if (!$correlativeRecord) {
            Log::warning("Correlativo no existe para la branch_id: {$this->branchId}");
            return false;
        }

        // 2. Obtener datos y validar
        $counter = (int) $correlativeRecord->counter;
        $final = (int) $correlativeRecord->final;
        $prefix = $correlativeRecord->prefix;
        $possibleCorrelative = $this->getNextCounter($counter);

        if ($possibleCorrelative > $final) {
            Log::error("El correlativo {$possibleCorrelative} excede el límite {$final} para branch_id: {$this->branchId}");
            return false;
        }

        // 3. Actualizar el contador en el objeto (NO COMMIT)
        $correlativeRecord->counter = $possibleCorrelative;
        $correlativeRecord->save(); // Se guardará cuando el OrderController haga el commit

        // 4. Retornar el correlativo final
        $newCorrelativeFormatted = $this->formatCorrelative($possibleCorrelative);
        $finalCorrelative = $prefix . $newCorrelativeFormatted;

        return $finalCorrelative;
    }
}
