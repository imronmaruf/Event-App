<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil city_id Banda Aceh sebagai default
        $bandaAcehId = DB::table('cities')->where('name', 'Banda Aceh')->value('id');

        $units = [
            [
                'name'           => 'Unit Pusat',
                'slug'           => 'unit-pusat',
                'city_id'        => $bandaAcehId,
                'description'    => 'Unit kegiatan tingkat pusat / induk',
                'contact_person' => 'Admin Pusat',
                'contact_phone'  => '08000000000',
                'is_active'      => true,
            ],
            [
                'name'           => 'Unit Banda Aceh',
                'slug'           => 'unit-banda-aceh',
                'city_id'        => $bandaAcehId,
                'description'    => 'Unit kegiatan kota Banda Aceh',
                'contact_person' => 'Admin BNA',
                'contact_phone'  => '08111111111',
                'is_active'      => true,
            ],
            [
                'name'           => 'Unit Lhokseumawe',
                'slug'           => 'unit-lhokseumawe',
                'city_id'        => DB::table('cities')->where('name', 'Lhokseumawe')->value('id'),
                'description'    => 'Unit kegiatan kota Lhokseumawe',
                'contact_person' => 'Admin LSM',
                'contact_phone'  => '08222222222',
                'is_active'      => true,
            ],
        ];

        foreach ($units as $unit) {
            DB::table('units')->updateOrInsert(
                ['slug' => $unit['slug']],
                array_merge($unit, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ UnitSeeder: ' . count($units) . ' unit berhasil di-seed.');
    }
}
