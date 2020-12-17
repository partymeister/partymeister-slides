<?php

namespace Partymeister\Slides\Database\Seeders;

use Illuminate\Database\Seeder;
use Partymeister\Competitions\Models\OptionGroup;
use Partymeister\Core\Models\User;

/**
 * Class AccountsTableSeeder
 * @package Partymeister\Accounting\Database\Seeds
 */
class TransitionsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transitions = [
            [
                'name'             => 'Random',
                'identifier'       => 255,
                'default_duration' => 2000,
                'client_type'      => 'screens',
            ],
            [
                'name'             => 'Crossfade',
                'identifier'       => 0,
                'default_duration' => 2000,
                'client_type'      => 'screens',
            ],
            [
                'name'             => 'Glitch',
                'identifier'       => 1,
                'default_duration' => 2000,
                'client_type'      => 'screens',
            ],
            [
                'name'             => 'Revision Logo',
                'identifier'       => 2,
                'default_duration' => 2000,
                'client_type'      => 'screens',
            ],
            [
                'name'             => 'Turn page',
                'identifier'       => 3,
                'default_duration' => 2000,
                'client_type'      => 'screens',
            ],
            [
                'name'             => 'Crash zoom',
                'identifier'       => 4,
                'default_duration' => 2000,
                'client_type'      => 'screens',
            ],
            [
                'name'             => 'Cube world',
                'identifier'       => 5,
                'default_duration' => 2000,
                'client_type'      => 'screens',
            ],
            [
                'name'             => 'Revision 2018 NO TRANSITION',
                'identifier'       => 6,
                'default_duration' => 2000,
                'client_type'      => 'screens',
            ],


            [
                'name'             => 'Random',
                'identifier'       => 255,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Crossfade',
                'identifier'       => 0,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Rotate',
                'identifier'       => 1,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Speed',
                'identifier'       => 2,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Bounce',
                'identifier'       => 3,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Flip',
                'identifier'       => 4,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Pulse',
                'identifier'       => 5,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Schwomp',
                'identifier'       => 6,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Wobble',
                'identifier'       => 7,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Zoom',
                'identifier'       => 8,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Zoom 2',
                'identifier'       => 9,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],
            [
                'name'             => 'Roll',
                'identifier'       => 10,
                'default_duration' => 2000,
                'client_type'      => 'slidemeister-web',
            ],

        ];

        foreach ($transitions as $transition) {
            DB::table('transitions')->insert([
                'name'             => $transition[ 'name' ],
                'identifier'       => $transition[ 'identifier' ],
                'client_type'      => $transition[ 'client_type' ],
                'default_duration' => $transition[ 'default_duration' ],
                'created_by'       => User::get()->first()->id,
                'updated_by'       => User::get()->first()->id,
            ]);
        }
    }
}
