<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DATABASE SEEDER - BROILINK
 * 
 * Copy file ini ke: database/seeders/DatabaseSeeder.php
 * 
 * Jalankan dengan: php artisan db:seed
 * atau: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear existing data (optional)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('manual_data')->truncate();
        DB::table('iot_data')->truncate();
        DB::table('farm_config')->truncate();
        DB::table('request_log')->truncate();
        DB::table('farms')->truncate();
        DB::table('users')->where('user_id', '>', 1)->delete(); // Keep admin
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🌱 Seeding Roles...\n";
        $this->seedRoles();

        echo "👤 Seeding Users...\n";
        $this->seedUsers();

        echo "🏠 Seeding Farms...\n";
        $this->seedFarms();

        echo "⚙️ Seeding Farm Configs...\n";
        $this->seedFarmConfigs();

        echo "📡 Seeding IoT Data...\n";
        $this->seedIotData();

        echo "📝 Seeding Manual Data...\n";
        $this->seedManualData();

        echo "📮 Seeding Request Logs...\n";
        $this->seedRequestLogs();

        echo "✅ Database seeded successfully!\n";
        $this->printCredentials();
    }

    private function seedRoles()
    {
        $roles = [
            ['id' => 1, 'name' => 'Admin', 'description' => 'Akses penuh ke sistem dan konfigurasi'],
            ['id' => 2, 'name' => 'Owner', 'description' => 'Pemilik kandang dengan akses laporan dan pengaturan'],
            ['id' => 3, 'name' => 'Peternak', 'description' => 'Petugas lapangan untuk input data manual'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    private function seedUsers()
    {
        $users = [
            // Admin (password: password)
            [
                'user_id' => 1,
                'role_id' => 1,
                'username' => 'admin_utama',
                'email' => 'admin@broilink.com',
                'password' => '$2y$12$n4Sjb3nUYu8jd.lOBd.yquu3T.kX0FR0ebr3HJ37mh6IV0Aq80GE6',
                'name' => 'Admin Utama Broilink',
                'phone_number' => '08123456789',
                'status' => 'active',
            ],
            // Owner 1 (password: owner123)
            [
                'user_id' => 2,
                'role_id' => 2,
                'username' => 'owner_budi',
                'email' => 'budi@owner.com',
                'password' => Hash::make('owner123'),
                'name' => 'Budi Santoso',
                'phone_number' => '08123456781',
                'status' => 'active',
            ],
            // Owner 2 (password: owner123)
            [
                'user_id' => 3,
                'role_id' => 2,
                'username' => 'owner_andi',
                'email' => 'andi@owner.com',
                'password' => Hash::make('owner123'),
                'name' => 'Andi Wijaya',
                'phone_number' => '08123456782',
                'status' => 'active',
            ],
            // Peternak 1 (password: peternak123)
            [
                'user_id' => 4,
                'role_id' => 3,
                'username' => 'peternak_agus',
                'email' => 'agus@peternak.com',
                'password' => Hash::make('peternak123'),
                'name' => 'Agus Setiawan',
                'phone_number' => '08123456783',
                'status' => 'active',
            ],
            // Peternak 2 (password: peternak123)
            [
                'user_id' => 5,
                'role_id' => 3,
                'username' => 'peternak_joko',
                'email' => 'joko@peternak.com',
                'password' => Hash::make('peternak123'),
                'name' => 'Joko Susilo',
                'phone_number' => '08123456784',
                'status' => 'active',
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['user_id' => $user['user_id']],
                array_merge($user, [
                    'date_joined' => now(),
                    'last_login' => now(),
                ])
            );
        }
    }

    private function seedFarms()
    {
        $farms = [
            [
                'farm_id' => 1,
                'owner_id' => 2,
                'peternak_id' => 4,
                'farm_name' => 'Kandang Broiler A1',
                'location' => 'Yogyakarta, Sleman',
                'initial_population' => 5000,
                'initial_weight' => 0.04,
                'farm_area' => 200,
            ],
            [
                'farm_id' => 2,
                'owner_id' => 2,
                'peternak_id' => null,
                'farm_name' => 'Kandang Broiler A2',
                'location' => 'Yogyakarta, Sleman',
                'initial_population' => 4500,
                'initial_weight' => 0.04,
                'farm_area' => 180,
            ],
            [
                'farm_id' => 3,
                'owner_id' => 3,
                'peternak_id' => 5,
                'farm_name' => 'Kandang Broiler B1',
                'location' => 'Bantul, Yogyakarta',
                'initial_population' => 6000,
                'initial_weight' => 0.04,
                'farm_area' => 250,
            ],
        ];

        foreach ($farms as $farm) {
            DB::table('farms')->insert($farm);
        }
    }

    private function seedFarmConfigs()
    {
        $defaultConfig = [
            'suhu_normal_min' => 28.00,
            'suhu_normal_max' => 32.00,
            'suhu_kritis_rendah' => 26.00,
            'suhu_kritis_tinggi' => 35.00,
            'kelembapan_normal_min' => 60.00,
            'kelembapan_normal_max' => 70.00,
            'kelembapan_kritis_rendah' => 50.00,
            'kelembapan_kritis_tinggi' => 80.00,
            'amonia_max' => 20.00,
            'amonia_kritis' => 30.00,
            'pakan_min' => 50.00,
            'minum_min' => 100.00,
            'pertumbuhan_mingguan_min' => 0.50,
            'target_bobot' => 2.00,
        ];

        $farmIds = [1, 2, 3];

        foreach ($farmIds as $farmId) {
            foreach ($defaultConfig as $paramName => $value) {
                DB::table('farm_config')->insert([
                    'farm_id' => $farmId,
                    'parameter_name' => $paramName,
                    'value' => $value,
                ]);
            }
        }
    }

    private function seedIotData()
    {
        // Farm 1 - Last 24 hours
        for ($i = 1; $i <= 24; $i++) {
            DB::table('iot_data')->insert([
                'farm_id' => 1,
                'timestamp' => now()->subHours($i),
                'temperature' => 29.0 + (rand(0, 30) / 10), // 29.0 - 32.0
                'humidity' => 62.0 + (rand(0, 60) / 10), // 62.0 - 68.0
                'ammonia' => 14.0 + (rand(0, 40) / 10), // 14.0 - 18.0
                'data_source' => 'IOT',
            ]);
        }

        // Farm 2 - Last 12 hours
        for ($i = 1; $i <= 12; $i++) {
            DB::table('iot_data')->insert([
                'farm_id' => 2,
                'timestamp' => now()->subHours($i),
                'temperature' => 30.0 + (rand(0, 20) / 10),
                'humidity' => 65.0 + (rand(0, 40) / 10),
                'ammonia' => 16.0 + (rand(0, 30) / 10),
                'data_source' => 'IOT',
            ]);
        }

        // Farm 3 - Last 12 hours
        for ($i = 1; $i <= 12; $i++) {
            DB::table('iot_data')->insert([
                'farm_id' => 3,
                'timestamp' => now()->subHours($i),
                'temperature' => 29.5 + (rand(0, 15) / 10),
                'humidity' => 62.0 + (rand(0, 40) / 10),
                'ammonia' => 14.0 + (rand(0, 20) / 10),
                'data_source' => 'IOT',
            ]);
        }
    }

    private function seedManualData()
    {
        // Farm 1 - Last 7 days
        for ($i = 1; $i <= 7; $i++) {
            DB::table('manual_data')->insert([
                'farm_id' => 1,
                'user_id_input' => 4,
                'report_date' => now()->subDays($i)->toDateString(),
                'konsumsi_pakan' => 48.0 + $i,
                'konsumsi_air' => 96.0 + ($i * 2),
                'jumlah_kematian' => rand(0, 2),
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);
        }

        // Farm 3 - Last 5 days
        for ($i = 1; $i <= 5; $i++) {
            DB::table('manual_data')->insert([
                'farm_id' => 3,
                'user_id_input' => 5,
                'report_date' => now()->subDays($i)->toDateString(),
                'konsumsi_pakan' => 51.0 + $i,
                'konsumsi_air' => 102.0 + ($i * 2),
                'jumlah_kematian' => rand(0, 2),
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);
        }
    }

    private function seedRequestLogs()
    {
        DB::table('request_log')->insert([
            [
                'user_id' => 0, // Guest
                'sender_name' => 'Dani Prasetyo',
                'request_type' => 'akun_baru',
                'request_content' => json_encode([
                    'name' => 'Dani Prasetyo',
                    'phone' => '08123456785',
                    'description' => 'Saya ingin mendaftar sebagai peternak'
                ]),
                'status' => 'menunggu',
                'sent_time' => now(),
            ],
            [
                'user_id' => 2,
                'sender_name' => 'Budi Santoso',
                'request_type' => 'tambah_kandang',
                'request_content' => json_encode([
                    'farm_name' => 'Kandang A3',
                    'location' => 'Sleman',
                    'capacity' => 5000
                ]),
                'status' => 'diproses',
                'sent_time' => now()->subDays(2),
            ],
        ]);
    }

    private function printCredentials()
    {
        echo "\n";
        echo "========================================\n";
        echo "📝 LOGIN CREDENTIALS\n";
        echo "========================================\n";
        echo "Admin:\n";
        echo "  Email: admin@broilink.com\n";
        echo "  Password: password\n";
        echo "\n";
        echo "Owner (Budi):\n";
        echo "  Email: budi@owner.com\n";
        echo "  Password: owner123\n";
        echo "\n";
        echo "Owner (Andi):\n";
        echo "  Email: andi@owner.com\n";
        echo "  Password: owner123\n";
        echo "\n";
        echo "Peternak (Agus):\n";
        echo "  Email: agus@peternak.com\n";
        echo "  Password: peternak123\n";
        echo "\n";
        echo "Peternak (Joko):\n";
        echo "  Email: joko@peternak.com\n";
        echo "  Password: peternak123\n";
        echo "========================================\n";
    }
}
