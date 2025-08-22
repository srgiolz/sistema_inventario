<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Líneas de lenguaje de validación
    |--------------------------------------------------------------------------
    */

    'accepted'               => 'El campo :attribute debe ser aceptado.',
    'accepted_if'            => 'El campo :attribute debe ser aceptado cuando :other sea :value.',
    'active_url'             => 'El campo :attribute debe ser una URL válida.',
    'after'                  => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal'         => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha'                  => 'El campo :attribute sólo debe contener letras.',
    'alpha_dash'             => 'El campo :attribute sólo debe contener letras, números, guiones y guiones bajos.',
    'alpha_num'              => 'El campo :attribute sólo debe contener letras y números.',
    'array'                  => 'El campo :attribute debe ser un conjunto.',
    'before'                 => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal'        => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between'                => [
        'array'   => 'El campo :attribute debe tener entre :min y :max elementos.',
        'file'    => 'El campo :attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string'  => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean'                => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed'              => 'La confirmación de :attribute no coincide.',
    'date'                   => 'El campo :attribute no es una fecha válida.',
    'date_equals'            => 'El campo :attribute debe ser una fecha igual a :date.',
    'date_format'            => 'El campo :attribute debe coincidir con el formato :format.',
    'different'              => 'Los campos :attribute y :other deben ser diferentes.',
    'digits'                 => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between'         => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'email'                  => 'El campo :attribute debe ser un correo válido.',
    'exists'                 => 'El campo :attribute seleccionado no es válido.',
    'file'                   => 'El campo :attribute debe ser un archivo.',
    'filled'                 => 'El campo :attribute es obligatorio.',
    'gt'                     => [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'file'    => 'El campo :attribute debe ser mayor que :value kilobytes.',
        'string'  => 'El campo :attribute debe ser mayor que :value caracteres.',
        'array'   => 'El campo :attribute debe tener más de :value elementos.',
    ],
    'gte'                    => [
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
        'file'    => 'El campo :attribute debe ser mayor o igual que :value kilobytes.',
        'string'  => 'El campo :attribute debe ser mayor o igual que :value caracteres.',
        'array'   => 'El campo :attribute debe tener :value elementos o más.',
    ],
    'image'                  => 'El campo :attribute debe ser una imagen.',
    'in'                     => 'El campo :attribute no es válido.',
    'integer'                => 'El campo :attribute debe ser un número entero.',
    'max'                    => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'file'    => 'El campo :attribute no debe ser mayor que :max kilobytes.',
        'string'  => 'El campo :attribute no debe ser mayor que :max caracteres.',
        'array'   => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'min'                    => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file'    => 'El campo :attribute debe ser de al menos :min kilobytes.',
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'not_in'                 => 'El campo :attribute seleccionado no es válido.',
    'numeric'                => 'El campo :attribute debe ser un número.',
    'required'               => 'El campo :attribute es obligatorio.',
    'required_if'            => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless'        => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with'          => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_without'       => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'same'                   => 'Los campos :attribute y :other deben coincidir.',
    'size'                   => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'file'    => 'El campo :attribute debe tener :size kilobytes.',
        'string'  => 'El campo :attribute debe tener :size caracteres.',
        'array'   => 'El campo :attribute debe contener :size elementos.',
    ],
    'string'                 => 'El campo :attribute debe ser una cadena de texto.',
    'unique'                 => 'El campo :attribute ya ha sido registrado.',

    /*
    |--------------------------------------------------------------------------
    | Mensajes personalizados
    |--------------------------------------------------------------------------
    */
    'custom' => [
        'de_sucursal' => [
            'required'  => 'Debes seleccionar la sucursal de origen.',
            'different' => 'La sucursal de origen y destino deben ser diferentes.',
        ],
        'a_sucursal' => [
            'required' => 'Debes seleccionar la sucursal de destino.',
        ],
        'productos' => [
            'required' => 'Debes agregar al menos un producto al traspaso.',
        ],
        'productos.*.producto_id' => [
            'required' => 'Debes seleccionar un producto.',
            'exists'   => 'El producto seleccionado no existe.',
        ],
        'productos.*.cantidad' => [
            'required' => 'Debes ingresar una cantidad.',
            'numeric'  => 'La cantidad debe ser un número.',
            'min'      => 'La cantidad debe ser al menos :min.',
        ],
        'fecha' => [
            'required' => 'La fecha del traspaso es obligatoria.',
            'date'     => 'La fecha no tiene un formato válido.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atributos personalizados
    |--------------------------------------------------------------------------
    */
    'attributes' => [
        'de_sucursal'           => 'sucursal de origen',
        'a_sucursal'            => 'sucursal de destino',
        'fecha'                 => 'fecha',
        'observacion'           => 'observación',
        'productos'             => 'productos',
        'productos.*.producto_id' => 'producto',
        'productos.*.cantidad'    => 'cantidad',
    ],

];
