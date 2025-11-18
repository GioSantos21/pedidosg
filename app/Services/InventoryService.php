<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = env('ADMINPOS_API_URL');
        $this->token = env('ADMINPOS_API_TOKEN');
    }

    /**
     * Obtiene el inventario en tiempo real de una sucursal externa.
     * @param string $branchExternalCode El UUID de la sucursal
     * @return array Lista de productos con su stock
     */
    public function getStock(string $branchExternalCode)
    {
        // 1. Definir la clave de caché y el tiempo de expiración (10 minutos)
        $cacheKey = 'inventory_stock_' . $branchExternalCode;
        $expirationTime = now()->addMinutes(10);

        // 2. Comprobar si el caché de sesión existe y es válido (Punto 4)
        if (session()->has($cacheKey) && session($cacheKey)['expires_at'] > now()) {
            Log::info("Inventario cargado desde la sesión caché.");
            return session($cacheKey)['data'];
        }

        // 3. Si no existe o expiró, llamar a la API
        try {
            $response = Http::withToken($this->token)
                ->timeout(10)
                ->get("{$this->baseUrl}/inventario/bodega_tiempo_real/{$branchExternalCode}");

            if ($response->successful()) {
                $apiResponse = $response->json();

                // 4. Guardar la respuesta junto con el tiempo de expiración (Punto 2 y 3)
                session([
                    $cacheKey => [
                        'data' => $apiResponse,
                        'expires_at' => $expirationTime,
                    ]
                ]);
                Log::info("Inventario cargado desde API y guardado en sesión.");

                return $apiResponse;
            } else {
                Log::error("Error API Inventario: " . $response->status());
                return ['data' => ['bodega' => []]]; // Devolver estructura vacía
            }
        } catch (\Exception $e) {
            Log::error("Excepción API Inventario: " . $e->getMessage());
            return ['data' => ['bodega' => []]]; // Devolver estructura vacía
        }
    }
    }
