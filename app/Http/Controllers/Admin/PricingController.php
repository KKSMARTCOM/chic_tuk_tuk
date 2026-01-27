<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pricing;
use App\Services\PricingService;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    public function index()
    {
        $pricings = Pricing::latest()->paginate(20);
        return view('admin.pricing.index', compact('pricings'));
    }

    public function create()
    {
        return view('admin.pricing.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'estimated_duration' => 'required|integer|min:1',
        ]);

        Pricing::create($validated);

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Tarif créé avec succès');
    }

    public function edit(Pricing $pricing)
    {
        return view('admin.pricing.edit', compact('pricing'));
    }

    public function update(Request $request, Pricing $pricing)
    {
        $validated = $request->validate([
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'estimated_duration' => 'required|integer|min:1',
        ]);

        $pricing->update($validated);

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Tarif mis à jour avec succès');
    }

    public function destroy(Pricing $pricing)
    {
        $pricing->delete();
        return redirect()->route('admin.pricing.index')
            ->with('success', 'Tarif supprimé avec succès');
    }
}
