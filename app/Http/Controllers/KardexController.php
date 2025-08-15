<?php

namespace App\Http\Controllers;

use App\Models\Kardex;
use App\Models\Sucursal;
use App\Models\Producto;
use Illuminate\Http\Request;

class KardexController extends Controller
{
    public function index(Request $request)
    {
        $query = Kardex::with(['producto', 'sucursal'])
            ->orderBy('fecha', 'desc');

        // Filtros
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        $kardex = $query->paginate(20);
        $productos = Producto::all();
        $sucursales = Sucursal::all();

        return view('kardex.index', compact('kardex', 'productos', 'sucursales'));
    }
}
