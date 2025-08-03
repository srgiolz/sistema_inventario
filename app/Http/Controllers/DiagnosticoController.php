<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnostico;

class DiagnosticoController extends Controller
{
    public function index()
    {
        $diagnosticos = Diagnostico::all();
        return view('diagnosticos.index', compact('diagnosticos'));
    }

    public function create()
    {
        return view('diagnosticos.create');
    }

   public function store(Request $request)
{
    $request->validate([
        'descripcion' => 'required|string|max:255',
    ]);

    // Convertimos la descripción a mayúsculas
    $data = $request->all();
    $data['descripcion'] = strtoupper($data['descripcion']);

    Diagnostico::create($data);

    return redirect()->route('diagnosticos.index')->with('success', 'Diagnóstico registrado correctamente.');
}


    public function destroy($id)
    {
        Diagnostico::findOrFail($id)->delete();
        return redirect()->route('diagnosticos.index')->with('success', 'Diagnóstico eliminado correctamente.');
    }
}
