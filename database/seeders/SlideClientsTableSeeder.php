<?php

namespace Partymeister\Slides\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Partymeister\Core\Models\User;

/**
 * Class AccountsTableSeeder
 */
class SlideClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('slide_clients')->insert([
            'name'          => 'Main Screen',
            'type'          => 'slidemeister-web',
            'sort_position' => 1,
            'configuration' => json_encode(['prizegiving_bar_color' => '#000000', 'prizegiving_bar_blink_color' => '#FF0000']),
            'created_by'    => User::get()->first()->id,
            'updated_by'    => User::get()->first()->id,
        ]);
    }
}
