<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class UpazilaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Upazila::unguard();
        // $upazilaSqlPath = public_path('sql_files/upazilas.sql');
        // DB::unprepared(file_get_contents($upazilaSqlPath));

        DB::disableQueryLog();
        LazyCollection::make(function(){
            $handle = fopen(public_path('sql_files/upazilas.csv'),'r');
            while(($line = fgetcsv($handle,4096)) != false){
                $dataString = implode(', ',$line);
                $row = explode(',',$dataString);
                yield $row;
            }
            fclose($handle);
        })
        ->skip(1)
        ->chunk(100)
        ->each(function(LazyCollection $chunk){
            $records = $chunk->map(function($row){
                return [
                    'zila_id' => $row[1],
                    'name' => $row[2],
                    'bn_name' => $row[3],
                    'url' => $row[4],
                ];
            })->toArray();

            DB::table('upazilas')->insert($records);
            
        });
    }
}
