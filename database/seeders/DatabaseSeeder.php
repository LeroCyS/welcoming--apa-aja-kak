<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        School::firstOrCreate(['name' => 'SMA Harapan Bangsa'], [
            'address' => 'Jl. Pendidikan No. 17, Makassar',
            'timezone' => 'Asia/Makassar',
            'default_service_start' => '07:00:00',
            'default_service_end' => '15:00:00',
        ]);

        $this->call([
            ReferenceSeeder::class,
            UserSeeder::class,
            SchoolDirectorySeeder::class,
            ArticleSeeder::class,
            DemoSeeder::class,
            QaAccountSeeder::class,
        ]);
    }
}
