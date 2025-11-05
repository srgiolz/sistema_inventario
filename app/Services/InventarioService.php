<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Kardex;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    // 🔹 Enumeraciones de tipo de movimiento
    const TIPO_ENTRADA          = 'ENTRADA';
    const TIPO_SALIDA           = 'SALIDA';
    const TIPO_TRASPASO_IN      = 'TRASPASO_IN';
    const TIPO_TRASPASO_OUT     = 'TRASPASO_OUT';
    const TIPO_ANULACION        = 'ANULACION';

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

        $docRef = strtoupper($documentoTipo) . ' #' . $documentoId;

        self::registrarKardex(
            $sucursalId,
            $productoId,
            $cantidad,
            self::TIPO_ENTRADA,
            $inventario->cantidad,
            $precio,
            $documentoTipo,
            $documentoId,
            $usuarioId,
            $docRef
        );

        return $inventario;
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

        if (!$inventario || $inventario->cantidad < $cantidad) {
            return false; // stock insuficiente
        }

        $inventario->cantidad -= $cantidad;
        $inventario->save();

        $docRef = strtoupper($documentoTipo) . ' #' . $documentoId;

        self::registrarKardex(
            $sucursalId,
            $productoId,
            -$cantidad,
            self::TIPO_SALIDA,
            $inventario->cantidad,
            $precio,
            $documentoTipo,
            $documentoId,
            $usuarioId,
            $docRef
        );

        return $inventario;
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

        $docRef = 'ANULACION ' . strtoupper($documentoTipo) . ' #' . $documentoId;

        self::registrarKardex(
            $sucursalId,
            $productoId,
            -$cantidad,
            self::TIPO_ANULACION . '_' . strtoupper($documentoTipo),
            $inventario->cantidad,
            $precio,
            $documentoTipo,
            $documentoId,
            $usuarioId,
            $docRef
        );

        return $inventario;
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

    // ======================================================
    // 🔹 NUEVAS FUNCIONALIDADES A PARTIR DE AQUÍ
    // ======================================================

    /**
     * Movimiento de Traspaso: resta del origen y suma al destino
     */
    public static function movimientoTraspaso($traspaso, $usuarioId)
    {
        DB::transaction(function () use ($traspaso, $usuarioId) {
            foreach ($traspaso->detalles as $detalle) {
                // OUT desde sucursal origen
                self::salidaNormal(
                    $traspaso->sucursal_origen_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'TRASPASO',
                    $traspaso->id,
                    $usuarioId
                );

                // IN en sucursal destino
                self::entradaNormal(
                    $traspaso->sucursal_destino_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'TRASPASO',
                    $traspaso->id,
                    $usuarioId
                );
            }
        });
    }

    /**
     * Consulta rápida de stock actual
     */
    public static function getStockActual($productoId, $sucursalId)
    {
        $inventario = Inventario::where('producto_id', $productoId)
            ->where('sucursal_id', $sucursalId)
            ->first();

        return $inventario ? $inventario->cantidad : 0;
    }

    /**
     * Valida si hay stock suficiente para varios productos
     */
    public static function bulkValidateStock($sucursalId, array $items)
    {
        foreach ($items as $item) {
            $inventario = Inventario::where('producto_id', $item['producto_id'])
                ->where('sucursal_id', $sucursalId)
                ->first();

            if (!$inventario || $inventario->cantidad < $item['cantidad']) {
                return false;
            }
        }
        return true;
    }
}

