<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Banda Aceh',    'province' => 'Aceh'],
            ['name' => 'Lhokseumawe',   'province' => 'Aceh'],
            ['name' => 'Langsa',        'province' => 'Aceh'],
            ['name' => 'Sabang',        'province' => 'Aceh'],
            ['name' => 'Subulussalam',  'province' => 'Aceh'],
            ['name' => 'Aceh Besar',    'province' => 'Aceh'],
            ['name' => 'Aceh Utara',    'province' => 'Aceh'],
            ['name' => 'Aceh Selatan',  'province' => 'Aceh'],
            ['name' => 'Aceh Timur',    'province' => 'Aceh'],
            ['name' => 'Pidie',         'province' => 'Aceh'],
        ];

        foreach ($cities as $city) {
            DB::table('cities')->updateOrInsert(
                ['name' => $city['name']],
                array_merge($city, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ CitySeeder: ' . count($cities) . ' kota berhasil di-seed.');
    }
}
