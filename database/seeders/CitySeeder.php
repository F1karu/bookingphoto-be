<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run()
    {
        $data = json_decode(file_get_contents(storage_path('app/regions.json')), true);

        foreach ($data as $provinsiData) {
            $provinsi = $provinsiData['provinsi'];

            foreach ($provinsiData['kota'] as $kota) {
                DB::table('cities')->insert([
                    'province' => $provinsi,
                    'name'     => $kota,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
