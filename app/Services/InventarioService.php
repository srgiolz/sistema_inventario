<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Kardex;

class InventarioService
{
    /**
     * Suma stock (Entradas, Traspaso destino)
     */
    public static function entradaNormal($sucursalId, $productoId, $cantidad, $precio, $documentoTipo, $documentoId, $usuarioId)
    {
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

        // 🔹 Referencia legible
        $docRef = strtoupper($documentoTipo) . ' #' . $documentoId;

        self::registrarKardex(
            $sucursalId,
            $productoId,
            $cantidad,
            'ENTRADA',
            $inventario->cantidad,
            $precio,
            $documentoTipo,
            $documentoId,
            $usuarioId,
            $docRef
        );

        return true;
    }

    /**
     * Resta stock (Salidas, Traspaso origen)
     */
    public static function salidaNormal($sucursalId, $productoId, $cantidad, $precio, $documentoTipo, $documentoId, $usuarioId)
    {
        if ($cantidad <= 0) {
            throw new \Exception("La cantidad debe ser mayor a 0");
        }

        $inventario = Inventario::where('sucursal_id', $sucursalId)
            ->where('producto_id', $productoId)
            ->lockForUpdate()
            ->first();

        // 🚨 Validación de stock
        if (!$inventario || $inventario->cantidad < $cantidad) {
            return false; // ahora sí se devuelve al controlador
        }

        // ✅ Restar stock
        $inventario->cantidad -= $cantidad;
        $inventario->save();

        // 🔹 Referencia legible
        $docRef = strtoupper($documentoTipo) . ' #' . $documentoId;

        self::registrarKardex(
            $sucursalId,
            $productoId,
            -$cantidad,
            'SALIDA',
            $inventario->cantidad,
            $precio,
            $documentoTipo,
            $documentoId,
            $usuarioId,
            $docRef
        );

        return true;
    }

    /**
     * Anula un movimiento confirmado (Ej: Anulación de Entrada o Salida)
     */
    public static function anulacion($sucursalId, $productoId, $cantidad, $precio, $documentoTipo, $documentoId, $usuarioId)
    {
        if ($cantidad <= 0) {
            throw new \Exception("La cantidad debe ser mayor a 0");
        }

        $inventario = Inventario::where('sucursal_id', $sucursalId)
            ->where('producto_id', $productoId)
            ->lockForUpdate()
            ->first();

        if (!$inventario || $inventario->cantidad < $cantidad) {
            throw new \Exception("Stock insuficiente para anular la operación");
        }

        $inventario->cantidad -= $cantidad;
        $inventario->save();

        // 🔹 Referencia legible para anulaciones
        $docRef = 'ANULACION ' . strtoupper($documentoTipo) . ' #' . $documentoId;

        self::registrarKardex(
            $sucursalId,
            $productoId,
            -$cantidad,
            'ANULACION_' . strtoupper($documentoTipo),
            $inventario->cantidad,
            $precio,
            $documentoTipo,
            $documentoId,
            $usuarioId,
            $docRef
        );

        return true;
    }

    /**
     * Registrar movimiento en Kardex
     */
    private static function registrarKardex($sucursalId, $productoId, $cantidad, $tipoMovimiento, $stockFinal, $precio, $documentoTipo, $documentoId, $usuarioId, $docRef = null)
    {
        Kardex::create([
            'sucursal_id'     => $sucursalId,
            'producto_id'     => $productoId,
            'cantidad'        => $cantidad,
            'tipo_movimiento' => $tipoMovimiento,
            'stock_final'     => $stockFinal,
            'precio'          => $precio,
            'documento_tipo'  => $documentoTipo,
            'documento_id'    => $documentoId,
            'usuario_id'      => $usuarioId,
            'fecha'           => now(),
            'doc_ref'         => $docRef ?? (strtoupper($documentoTipo) . ' #' . $documentoId),
        ]);
    }
}

