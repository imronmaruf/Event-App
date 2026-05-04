<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with('city')->withCount('events')->orderBy('name')->paginate(20);
        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        $cities = City::orderBy('name')->get();
        return view('admin.units.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:150',
            'slug'            => 'nullable|string|max:100|unique:units,slug',
            'city_id'         => 'nullable|exists:cities,id',
            'description'     => 'nullable|string|max:255',
            'contact_person'  => 'nullable|string|max:100',
            'contact_phone'   => 'nullable|string|max:20',
            'is_active'       => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $validated['is_active'] = $request->boolean('is_active', true);

        Unit::create($validated);
        return redirect()->route('admin.units.index')->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        $cities = City::orderBy('name')->get();
        return view('admin.units.edit', compact('unit', 'cities'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'slug'           => "nullable|string|max:100|unique:units,slug,{$unit->id}",
            'city_id'        => 'nullable|exists:cities,id',
            'description'    => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:100',
            'contact_phone'  => 'nullable|string|max:20',
            'is_active'      => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $validated['is_active'] = $request->boolean('is_active');

        $unit->update($validated);
        return redirect()->route('admin.units.index')->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->events()->exists()) {
            return back()->with('error', 'Unit tidak bisa dihapus karena masih memiliki event.');
        }
        $unit->delete();
        return back()->with('success', 'Unit berhasil dihapus.');
    }
}

// ════════════════════════════════════════════════════════════════
// Taruh di file terpisah: app/Http/Controllers/Admin/CityController.php
// Untuk kepraktisan di sini dijadikan 1 file, pisahkan saat deploy
// ════════════════════════════════════════════════════════════════
