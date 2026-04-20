<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParcelStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['key' => 'ready',      'name' => 'جاهز',        'color' => '#6366f1', 'is_default' => true],
            ['key' => 'delivered',  'name' => 'تم تسليمه',   'color' => '#22c55e', 'is_default' => false],
            ['key' => 'in_transit', 'name' => 'في الطريق',   'color' => '#eab308', 'is_default' => false],
            ['key' => 'returned',   'name' => 'راجع',        'color' => '#94a3b8', 'is_default' => false],
            ['key' => 'damaged',    'name' => 'متلف',        'color' => '#ef4444', 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            \App\Models\ParcelStatus::updateOrCreate(['key' => $status['key']], $status);
        }
    }
}
