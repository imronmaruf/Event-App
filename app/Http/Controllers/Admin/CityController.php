<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::withCount('units')->orderBy('name')->paginate(50);
        return view('admin.cities.index', compact('cities'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100|unique:cities,name',
            'province' => 'nullable|string|max:100',
        ], [
            'name.required' => 'Nama kota wajib diisi.',
            'name.unique'   => 'Nama kota sudah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $city = City::create([
            'name'     => trim($request->name),
            'province' => trim($request->province ?? ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Kota \"{$city->name}\" berhasil ditambahkan.",
            'city'    => $city,
        ]);
    }

    public function update(Request $request, City $city)
    {
        $validator = Validator::make($request->all(), [
            'name'     => "required|string|max:100|unique:cities,name,{$city->id}",
            'province' => 'nullable|string|max:100',
        ], [
            'name.required' => 'Nama kota wajib diisi.',
            'name.unique'   => 'Nama kota sudah terdaftar.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $city->update([
            'name'     => trim($request->name),
            'province' => trim($request->province ?? ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Kota \"{$city->name}\" berhasil diperbarui.",
            'city'    => $city,
        ]);
    }

    public function destroy(City $city)
    {
        if ($city->units()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Kota \"{$city->name}\" tidak bisa dihapus karena masih memiliki {$city->units()->count()} unit terkait.",
            ], 422);
        }

        $name = $city->name;
        $city->delete();

        return response()->json([
            'success' => true,
            'message' => "Kota \"{$name}\" berhasil dihapus.",
        ]);
    }
}
