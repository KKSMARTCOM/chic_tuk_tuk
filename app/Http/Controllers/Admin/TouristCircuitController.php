<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TouristCircuit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TouristCircuitController extends Controller
{
    public function index(Request $request)
    {
        $query = TouristCircuit::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->get('is_active'));
        }

        $circuits = $query->latest()->paginate(15);
        $stats = [
            'total' => TouristCircuit::count(),
            'active' => TouristCircuit::where('is_active', true)->count(),
            'inactive' => TouristCircuit::where('is_active', false)->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json($circuits);
        }

        return view('pages.admin.circuits.index', compact('circuits', 'stats'));
    }

    public function create()
    {
        return view('pages.admin.circuits.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'description' => 'required|string|min:10',
                'locations' => 'required|array|min:1',
                'locations.*' => 'required|string|max:255',
                'price' => 'required|numeric|min:0|decimal:0,2',
                'duration' => 'required|integer|min:1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'boolean',
            ],
            [
                'name.required' => 'Le nom du circuit est requis.',
                'description.required' => 'La description est requise.',
                'description.min' => 'La description doit contenir au moins 10 caractères.',
                'locations.required' => 'Vous devez ajouter au moins un point d\'intérêt.',
                'locations.*.required' => 'Tous les points d\'intérêt doivent être remplis.',
                'price.required' => 'Le prix est requis.',
                'price.numeric' => 'Le prix doit être un nombre.',
                'price.min' => 'Le prix doit être positif.',
                'duration.required' => 'La durée est requise.',
                'duration.integer' => 'La durée doit être un nombre entier.',
                'duration.min' => 'La durée doit être au minimum 1 heure.',
                'image.image' => 'Le fichier doit être une image.',
                'image.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
                'image.max' => 'L\'image ne doit pas dépasser 2MB.',
            ]
        );

        try {
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('circuits', 'public');
            }

            $validated['is_active'] = $request->has('is_active');

            TouristCircuit::create($validated);

            return redirect()->route('admin.circuits.index')
                ->with('success', 'Circuit touristique créé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur s\'est produite lors de la création du circuit');
        }
    }

    public function show(TouristCircuit $circuit)
    {
        return view('pages.admin.circuits.show', compact('circuit'));
    }

    public function edit(TouristCircuit $circuit)
    {
        return view('pages.admin.circuits.edit', compact('circuit'));
    }

    public function update(Request $request, TouristCircuit $circuit)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'description' => 'required|string|min:10',
                'locations' => 'required|array|min:1',
                'locations.*' => 'required|string|max:255',
                'price' => 'required|numeric|min:0|decimal:0,2',
                'duration' => 'required|integer|min:1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'boolean',
            ],
            [
                'name.required' => 'Le nom du circuit est requis.',
                'description.required' => 'La description est requise.',
                'description.min' => 'La description doit contenir au moins 10 caractères.',
                'locations.required' => 'Vous devez ajouter au moins un point d\'intérêt.',
                'locations.*.required' => 'Tous les points d\'intérêt doivent être remplis.',
                'price.required' => 'Le prix est requis.',
                'price.numeric' => 'Le prix doit être un nombre.',
                'price.min' => 'Le prix doit être positif.',
                'duration.required' => 'La durée est requise.',
                'duration.integer' => 'La durée doit être un nombre entier.',
                'duration.min' => 'La durée doit être au minimum 1 heure.',
                'image.image' => 'Le fichier doit être une image.',
                'image.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
                'image.max' => 'L\'image ne doit pas dépasser 2MB.',
            ]
        );

        try {
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image si elle existe
                if ($circuit->image && Storage::disk('public')->exists($circuit->image)) {
                    Storage::disk('public')->delete($circuit->image);
                }
                $validated['image'] = $request->file('image')->store('circuits', 'public');
            }

            $validated['is_active'] = $request->has('is_active');

            $circuit->update($validated);

            return redirect()->route('admin.circuits.index')
                ->with('success', 'Circuit touristique modifié avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur s\'est produite lors de la modification du circuit');
        }
    }

    public function destroy(TouristCircuit $circuit)
    {
        try {
            // Supprimer l'image si elle existe
            if ($circuit->image && Storage::disk('public')->exists($circuit->image)) {
                Storage::disk('public')->delete($circuit->image);
            }

            $circuit->delete();

            return redirect()->route('admin.circuits.index')
                ->with('success', 'Circuit touristique supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur s\'est produite lors de la suppression du circuit');
        }
    }

    public function toggleStatus(Request $request, TouristCircuit $circuit)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $circuit->update(['is_active' => $validated['is_active']]);

        return response()->json([
            'success' => true,
            'message' => $validated['is_active'] ? 'Circuit activé avec succès' : 'Circuit désactivé avec succès',
            'is_active' => $circuit->is_active,
        ]);
    }
}
