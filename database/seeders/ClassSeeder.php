<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table("class")->truncate();
        Schema::enableForeignKeyConstraints();
        $classes = [
            ['class_name'=> 'X RPL'],
            ['class_name'=> 'XI RPL 1' ],
            ['class_name'=> 'XI RPL 2' ],
            ['class_name'=> 'XII RPL' ],
            ['class_name'=> 'X TKJ' ],
            ['class_name'=> 'XI TKJ' ],
            ['class_name'=> 'XII TKJ 1' ],
            ['class_name'=> 'XII TKJ 2' ]
        ];

        foreach ($classes as $value) {
        DB::table('class')->insert([
            'class_name'=> $value['class_name'],
            'created_at'=> Carbon::now(),
        ]);
        }
    }
}
