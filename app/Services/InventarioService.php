<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Inventario;
use App\Models\Kardex;

class InventarioService
{
    /**
     * Suma stock (Entradas, Traspaso destino)
     */
    public static function entradaNormal($sucursalId, $productoId, $cantidad, $precio, $documentoTipo, $documentoId, $usuarioId, $observacion = null)
{
    DB::transaction(function () use ($sucursalId, $productoId, $cantidad, $precio, $documentoTipo, $documentoId, $usuarioId, $observacion) {
        if ($cantidad <= 0) {
            throw new \Exception("La cantidad debe ser mayor a 0");
        }

        $inventario = Inventario::where('sucursal_id', $sucursalId)
            ->where('producto_id', $productoId)
            ->lockForUpdate()
            ->first();

        if (!$inventario) {
            $inventario = new Inventario();
            $inventario->sucursal_id = $sucursalId;
            $inventario->producto_id = $productoId;
            $inventario->cantidad = 0;
        }

        $inventario->cantidad += $cantidad;
        $inventario->save();

        self::registrarKardex($sucursalId, $productoId, $cantidad, 'Entrada', $inventario->cantidad, $precio, $documentoTipo, $documentoId, $usuarioId, $observacion);
    });
}

public static function salidaNormal($sucursalId, $productoId, $cantidad, $precio, $documentoTipo, $documentoId, $usuarioId, $observacion = null)
{
    DB::transaction(function () use ($sucursalId, $productoId, $cantidad, $precio, $documentoTipo, $documentoId, $usuarioId, $observacion) {
        if ($cantidad <= 0) {
            throw new \Exception("La cantidad debe ser mayor a 0");
        }

        $inventario = Inventario::where('sucursal_id', $sucursalId)
            ->where('producto_id', $productoId)
            ->lockForUpdate()
            ->first();

        if (!$inventario || $inventario->cantidad < $cantidad) {
            throw new \Exception("Stock insuficiente para realizar la operación");
        }

        $inventario->cantidad -= $cantidad;
        $inventario->save();

        self::registrarKardex($sucursalId, $productoId, -$cantidad, 'Salida', $inventario->cantidad, $precio, $documentoTipo, $documentoId, $usuarioId, $observacion);
    });
}

private static function registrarKardex($sucursalId, $productoId, $cantidad, $tipoMovimiento, $stockFinal, $precio, $documentoTipo, $documentoId, $usuarioId, $observacion = null)
{
    Kardex::create([
        'sucursal_id'      => $sucursalId,
        'producto_id'      => $productoId,
        'cantidad'         => $cantidad,
        'tipo_movimiento'  => $tipoMovimiento,
        'stock_final'      => $stockFinal,
        'precio'           => $precio,
        'documento_tipo'   => $documentoTipo,
        'documento_id'     => $documentoId,
        'usuario_id'       => $usuarioId,
        'fecha'            => now(),
        'observacion'      => $observacion
    ]);
}

}
