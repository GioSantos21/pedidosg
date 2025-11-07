<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Líneas de Lenguaje de Validación
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar los mensajes de error personalizados para tu aplicación.
    |
    */

    'confirmed' => 'La confirmación de :attribute no coincide.',

    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        // También puedes añadir 'number' por si acaso
        'number' => 'El campo :attribute debe ser de al menos :min.',
    ],

    'current_password' => 'La :attribute actual es incorrecta.',

    // AÑADE ESTA CLAVE
    'required' => 'El campo :attribute es obligatorio.',

    // ¡BONUS! Esto traduce los nombres de los campos
    // para que no diga "El campo password..." sino "El campo contraseña..."
    'attributes' => [
        'password' => 'contraseña',
        'email' => 'correo electrónico',
        'name' => 'nombre',
        'current_password' => 'contraseña'
    ],

];
