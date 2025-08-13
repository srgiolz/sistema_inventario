<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Models\Inventario;

class ProductoController extends Controller
{
    // Mostrar lista de productos
    public function index()
    {
        $productos = Producto::all();
        return view('productos.index', compact('productos'));
    }

    // Mostrar formulario de registro
    public function create()
    {
        return view('productos.create');
    }

    // Guardar nuevo producto
    public function store(Request $request)
    {
        $request->validate([
            'codigo_item' => 'required|unique:productos',  // Cambié 'codigo_item' por 'codigo_item'
            'descripcion' => 'required',
        ]);

        Producto::create(array_map('mb_strtoupper', $request->except(['precio_costo', 'precio_venta'])) + $request->only(['precio_costo', 'precio_venta']));

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    // Mostrar formulario de edición
    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        return view('productos.edit', compact('producto'));
    }

    // Actualizar producto
    public function update(Request $request, $id)
    {
        $request->validate([
            'codigo_item' => 'required|unique:productos,codigo_item,' . $id,  // Cambié 'codigo_item' por 'codigo_item'
            'descripcion' => 'required',
        ]);

        $producto = Producto::findOrFail($id);

        $data = array_map('mb_strtoupper', $request->except(['precio_costo', 'precio_venta'])) + $request->only(['precio_costo', 'precio_venta']);

        $producto->update($data);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    // Eliminar producto
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }

    // Vista de inventario con filtros y stock por sucursal
    public function inventario(Request $request)
    {
        $productos = Producto::with('inventarios');

        // Aplicar filtros
        if ($request->codigo) {
            $productos->where('codigo_item', 'like', '%' . $request->codigo . '%');  // Cambié 'codigo_item' por 'codigo_item'
        }

        if ($request->descripcion) {
            $productos->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }

        if ($request->linea) {
            $productos->where('linea', $request->linea);
        }

        $productos = $productos->get();
        $sucursales = Sucursal::all();

        // Calcular stock por sucursal y total
        foreach ($productos as $producto) {
            $stock_por_sucursal = [];

            foreach ($sucursales as $sucursal) {
                $cantidad = Inventario::where('producto_id', $producto->id)  // Cambié 'producto_id' por 'producto_id'
                    ->where('sucursal_id', $sucursal->id)  // Cambié 'sucursal_id' por 'sucursal_id'
                    ->value('cantidad') ?? 0;

                $stock_por_sucursal[$sucursal->id] = $cantidad;
            }

            $producto->stock_por_sucursal = $stock_por_sucursal;
            $producto->stock_total = array_sum($stock_por_sucursal);
        }

        $lineas = Producto::select('linea')->distinct()->pluck('linea');

        return view('productos.inventario', compact('productos', 'sucursales', 'lineas'));
    }
}

