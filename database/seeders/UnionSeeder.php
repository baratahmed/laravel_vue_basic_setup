<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class UnionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Union::unguard();
        // $unionSqlPath = public_path('sql_files/unions.sql');
        // DB::unprepared(file_get_contents($unionSqlPath));

        DB::disableQueryLog();
        LazyCollection::make(function(){
            $handle = fopen(public_path('sql_files/unions.csv'),'r');
            while(($line = fgetcsv($handle,4096)) != false){
                $dataString = implode(', ',$line);
                $row = explode(',',$dataString);
                yield $row;
            }
            fclose($handle);
        })
        ->skip(1)
        ->chunk(1000)
        ->each(function(LazyCollection $chunk){
            $records = $chunk->map(function($row){
                return [
                    'upazila_id' => $row[1],
                    'name' => $row[2],
                    'bn_name' => $row[3],
                    'url' => $row[4],
                ];
            })->toArray();

            DB::table('unions')->insert($records);
            
        });
    }
}
