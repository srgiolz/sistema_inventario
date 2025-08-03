<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medico;

class MedicoController extends Controller
{
    public function index()
    {
        $medicos = Medico::all();
        return view('medicos.index', compact('medicos'));
    }

    public function create()
    {
        return view('medicos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo_medico' => 'required|unique:medicos,codigo_medico',
            'nombre' => 'required|string|max:255',
            'especialidad' => 'nullable|string',
            'direccion' => 'nullable|string',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string',
        ]);

        Medico::create($request->all());

        return redirect()->route('medicos.index')->with('success', 'Médico registrado correctamente.');
    }

    public function destroy($id)
    {
        Medico::findOrFail($id)->delete();
        return redirect()->route('medicos.index')->with('success', 'Médico eliminado correctamente.');
    }
}
