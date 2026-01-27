<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TouristCircuit;
use Illuminate\Http\Request;

class TouristCircuitController extends Controller
{
    public function index()
    {
        $circuits = TouristCircuit::latest()->paginate(20);
        return view('admin.circuits.index', compact('circuits'));
    }

    public function create()
    {
        return view('admin.circuits.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'locations' => 'required|array',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('circuits', 'public');
        }

        TouristCircuit::create($validated);

        return redirect()->route('admin.circuits.index')
            ->with('success', 'Circuit touristique créé avec succès');
    }

    public function edit(TouristCircuit $circuit)
    {
        return view('admin.circuits.edit', compact('circuit'));
    }

    public function update(Request $request, TouristCircuit $circuit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'locations' => 'required|array',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('circuits', 'public');
        }

        $circuit->update($validated);

        return redirect()->route('admin.circuits.index')
            ->with('success', 'Circuit touristique mis à jour avec succès');
    }

    public function destroy(TouristCircuit $circuit)
    {
        $circuit->delete();
        return redirect()->route('admin.circuits.index')
            ->with('success', 'Circuit touristique supprimé avec succès');
    }
}
