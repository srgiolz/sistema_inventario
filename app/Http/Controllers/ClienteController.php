<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Medico;
use App\Models\Diagnostico;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::with('medico', 'diagnostico')->get();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $medicos = Medico::all();
        $diagnosticos = Diagnostico::all();
        return view('clientes.create', compact('medicos', 'diagnosticos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_cliente' => 'required|in:particular,paciente',
            'ci_nit' => 'nullable|string',
            'nombre' => 'required|string',
            'apellido' => 'nullable|string',
            'sexo' => 'nullable|string',
            'ciudad' => 'nullable|string',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string',
            'medico_id' => 'nullable|exists:medicos,id',  // Cambié 'id_medico' por 'medico_id'
            'diagnostico_id' => 'nullable|exists:diagnosticos,id',  // Cambié 'id_diagnostico' por 'diagnostico_id'
        ]);

        Cliente::create($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado correctamente.');
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        $medicos = Medico::all();
        $diagnosticos = Diagnostico::all();

        return view('clientes.edit', compact('cliente', 'medicos', 'diagnosticos'));
    }
    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tipo_cliente' => 'required|in:particular,paciente',
            'ci_nit' => 'nullable|string',
            'nombre' => 'required|string',
            'apellido' => 'nullable|string',
            'sexo' => 'nullable|string',
            'ciudad' => 'nullable|string',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string',
            'medico_id' => 'nullable|exists:medicos,id',  // Cambié 'id_medico' por 'medico_id'
            'diagnostico_id' => 'nullable|exists:diagnosticos,id',  // Cambié 'id_diagnostico' por 'diagnostico_id'
        ]);

        $cliente = Cliente::findOrFail($id);
        $cliente->update($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }
}


