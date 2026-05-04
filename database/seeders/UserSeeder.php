<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Ambil unit_id dari DB ─────────────────────────────
        $unitPusat  = DB::table('units')->where('slug', 'unit-pusat')->value('id');
        $unitBNA    = DB::table('units')->where('slug', 'unit-banda-aceh')->value('id');
        $unitLSM    = DB::table('units')->where('slug', 'unit-lhokseumawe')->value('id');

        // ══════════════════════════════════════════════════════
        //  SUPERADMIN
        //  → Bisa melihat & mengelola SEMUA unit, event, admin
        //  → unit_id = null (tidak terikat ke unit manapun)
        // ══════════════════════════════════════════════════════
        $superadmins = [
            [
                'name'       => 'Super Administrator',
                'email'      => 'superadmin@sipresensi.id',
                'password'   => Hash::make('superadmin123'),
                'role'       => 'superadmin',
                'unit_id'    => null,
                'is_active'  => true,
            ],
            [
                'name'       => 'Developer Admin',
                'email'      => 'dev@sipresensi.id',
                'password'   => Hash::make('devadmin123'),
                'role'       => 'superadmin',
                'unit_id'    => null,
                'is_active'  => true,
            ],
        ];

        // ══════════════════════════════════════════════════════
        //  ADMIN UNIT
        //  → Hanya bisa mengelola event & peserta di unit sendiri
        //  → unit_id wajib diisi
        // ══════════════════════════════════════════════════════
        $admins = [
            [
                'name'       => 'Admin Unit Pusat',
                'email'      => 'admin.pusat@sipresensi.id',
                'password'   => Hash::make('adminpusat123'),
                'role'       => 'admin',
                'unit_id'    => $unitPusat,
                'is_active'  => true,
            ],
            [
                'name'       => 'Admin Banda Aceh',
                'email'      => 'admin.bna@sipresensi.id',
                'password'   => Hash::make('adminbna123'),
                'role'       => 'admin',
                'unit_id'    => $unitBNA,
                'is_active'  => true,
            ],
            [
                'name'       => 'Admin Lhokseumawe',
                'email'      => 'admin.lsm@sipresensi.id',
                'password'   => Hash::make('adminlsm123'),
                'role'       => 'admin',
                'unit_id'    => $unitLSM,
                'is_active'  => true,
            ],
        ];

        $allUsers = array_merge($superadmins, $admins);
        $created  = 0;
        $skipped  = 0;

        foreach ($allUsers as $user) {
            $exists = DB::table('users')->where('email', $user['email'])->exists();

            if ($exists) {
                $skipped++;
                $this->command->warn("  ⚠  Email sudah ada, dilewati: {$user['email']}");
                continue;
            }

            DB::table('users')->insert(array_merge($user, [
                'remember_token' => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]));

            $created++;
            $label = $user['role'] === 'superadmin' ? '👑 SUPERADMIN' : '🔑 ADMIN';
            $this->command->info("  {$label} dibuat → {$user['email']}");
        }

        // ── Ringkasan ─────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║          USER SEEDER — RINGKASAN AKUN               ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  👑 SUPERADMIN                                       ║');
        $this->command->info('║  Email   : superadmin@sipresensi.id                  ║');
        $this->command->info('║  Password: superadmin123                             ║');
        $this->command->info('║                                                      ║');
        $this->command->info('║  👑 SUPERADMIN (Dev)                                 ║');
        $this->command->info('║  Email   : dev@sipresensi.id                         ║');
        $this->command->info('║  Password: devadmin123                               ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  🔑 ADMIN Unit Pusat                                 ║');
        $this->command->info('║  Email   : admin.pusat@sipresensi.id                 ║');
        $this->command->info('║  Password: adminpusat123                             ║');
        $this->command->info('║                                                      ║');
        $this->command->info('║  🔑 ADMIN Banda Aceh                                 ║');
        $this->command->info('║  Email   : admin.bna@sipresensi.id                   ║');
        $this->command->info('║  Password: adminbna123                               ║');
        $this->command->info('║                                                      ║');
        $this->command->info('║  🔑 ADMIN Lhokseumawe                                ║');
        $this->command->info('║  Email   : admin.lsm@sipresensi.id                   ║');
        $this->command->info('║  Password: adminlsm123                               ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info("║  ✅ Dibuat: {$created}  |  ⚠️  Dilewati: {$skipped}                         ║");
        $this->command->info('╚══════════════════════════════════════════════════════╝');
        $this->command->newLine();
        $this->command->warn('  ⚠️  PENTING: Ganti semua password di atas setelah deploy!');
    }
}
