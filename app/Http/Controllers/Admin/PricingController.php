<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pricing;
use App\Models\Zone;
use App\Services\PricingService;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    public function index(Request $request)
    {
        $query = Pricing::with(['fromZone', 'toZone']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('fromZone', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
                ->orWhereHas('toZone', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        $pricings = $query->paginate(10);
        $zones = Zone::orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json($pricings);
        }

        return view('pages.admin.pricings.index', compact('pricings', 'zones'));
    }

    public function create()
    {
        $zones = Zone::orderBy('name')->get();

        return view('pages.admin.pricings.create', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'from_zone_id' => 'required|uuid|exists:zones,id',
                'to_zone_id' => 'required|uuid|exists:zones,id|different:from_zone_id',
                'base_price' => 'required|numeric|min:0|decimal:0,2',
                'price_per_km' => 'nullable|numeric|min:0|decimal:0,2',
                'estimated_duration' => 'nullable|integer|min:1',
            ],
            [
                'from_zone_id.required' => 'La zone de départ est requise.',
                'from_zone_id.exists' => 'La zone de départ sélectionnée n\'existe pas.',
                'to_zone_id.required' => 'La zone de destination est requise.',
                'to_zone_id.exists' => 'La zone de destination sélectionnée n\'existe pas.',
                'to_zone_id.different' => 'Les zones de départ et de destination doivent être différentes.',
                'base_price.required' => 'Le prix de base est requis.',
                'base_price.numeric' => 'Le prix de base doit être un nombre.',
                'base_price.min' => 'Le prix de base doit être positif.',
                //'price_per_km.required' => 'Le prix par km est requis.',
                'price_per_km.numeric' => 'Le prix par km doit être un nombre.',
                'price_per_km.min' => 'Le prix par km doit être positif.',
                //'estimated_duration.required' => 'La durée estimée est requise.',
                'estimated_duration.integer' => 'La durée estimée doit être un nombre entier.',
                'estimated_duration.min' => 'La durée estimée doit être au minimum 1 minute.',
            ]
        );

        try {
            $data = [
                'from_zone_id' => $validated['from_zone_id'],
                'to_zone_id' => $validated['to_zone_id'],
                'base_price' => $validated['base_price'],
                'price_per_km' => $validated['price_per_km'] ?? 0,
                'estimated_duration' => $validated['estimated_duration'] ?? 0,
            ];

            Pricing::create($data);

            return redirect()->route('admin.pricing.index')
                ->with('success', 'Tarif ajouté avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur s\'est produite lors de l\'ajout du tarif' . $e->getMessage());
        }
    }

    public function show(Pricing $pricing)
    {
        $pricing->load(['fromZone', 'toZone']);

        return view('pages.admin.pricings.show', compact('pricing'));
    }

    public function edit(Pricing $pricing)
    {
        $pricing->load(['fromZone', 'toZone']);
        $zones = Zone::orderBy('name')->get();

        return view('pages.admin.pricings.edit', compact('pricing', 'zones'));
    }

    public function update(Request $request, Pricing $pricing)
    {
        $validated = $request->validate(
            [
                'from_zone_id' => 'required|uuid|exists:zones,id',
                'to_zone_id' => 'required|uuid|exists:zones,id|different:from_zone_id',
                'base_price' => 'required|numeric|min:0|decimal:0,2',
                'price_per_km' => 'nullable|numeric|min:0|decimal:0,2',
                'estimated_duration' => 'nullable|integer|min:1',
            ],
            [
                'from_zone_id.required' => 'La zone de départ est requise.',
                'from_zone_id.exists' => 'La zone de départ sélectionnée n\'existe pas.',
                'to_zone_id.required' => 'La zone de destination est requise.',
                'to_zone_id.exists' => 'La zone de destination sélectionnée n\'existe pas.',
                'to_zone_id.different' => 'Les zones de départ et de destination doivent être différentes.',
                'base_price.required' => 'Le prix de base est requis.',
                'base_price.numeric' => 'Le prix de base doit être un nombre.',
                'base_price.min' => 'Le prix de base doit être positif.',
                //'price_per_km.required' => 'Le prix par km est requis.',
                'price_per_km.numeric' => 'Le prix par km doit être un nombre.',
                'price_per_km.min' => 'Le prix par km doit être positif.',
                //'estimated_duration.required' => 'La durée estimée est requise.',
                'estimated_duration.integer' => 'La durée estimée doit être un nombre entier.',
                'estimated_duration.min' => 'La durée estimée doit être au minimum 1 minute.',
            ]
        );

        try {
            $data = [
                'from_zone_id' => $validated['from_zone_id'],
                'to_zone_id' => $validated['to_zone_id'],
                'base_price' => $validated['base_price'],
                'price_per_km' => $validated['price_per_km'] ?? $pricing->price_per_km,
                'estimated_duration' => $validated['estimated_duration'] ?? $pricing->estimated_duration,
            ];

            $pricing->update($data);

            return redirect()->route('admin.pricing.index')
                ->with('success', 'Tarif modifié avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur s\'est produite lors de la modification du tarif' . $e->getMessage());
        }
    }

    public function destroy(Pricing $pricing)
    {
        try {
            $pricing->delete();

            return redirect()->route('admin.pricing.index')
                ->with('success', 'Tarif supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur s\'est produite lors de la suppression du tarif' . $e->getMessage());
        }
    }
}
