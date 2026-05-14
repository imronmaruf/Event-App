<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::withCount('units')->orderBy('name')->paginate(20);
        return view('admin.cities.index', compact('cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100|unique:cities,name',
            'province' => 'nullable|string|max:100',
        ]);

        $city = City::create($request->only('name', 'province'));

        return response()->json([
            'message' => "Kota \"{$city->name}\" berhasil ditambahkan.",
            'city'    => $city,
        ]);
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'name'     => "required|string|max:100|unique:cities,name,{$city->id}",
            'province' => 'nullable|string|max:100',
        ]);

        $city->update($request->only('name', 'province'));

        return response()->json([
            'message' => "Kota \"{$city->name}\" berhasil diperbarui.",
            'city'    => $city,
        ]);
    }

    public function destroy(City $city)
    {
        if ($city->units()->exists()) {
            return response()->json([
                'message' => "Kota \"{$city->name}\" tidak bisa dihapus karena masih memiliki unit terkait.",
            ], 422);
        }

        $name = $city->name;
        $city->delete();

        return response()->json([
            'message' => "Kota \"{$name}\" berhasil dihapus.",
        ]);
    }
}
