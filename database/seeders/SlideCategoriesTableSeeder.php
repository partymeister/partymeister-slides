<?php

namespace Partymeister\Slides\Database\Seeders;

use Illuminate\Database\Seeder;
use Partymeister\Competitions\Models\OptionGroup;
use Partymeister\Core\Models\User;

/**
 * Class AccountsTableSeeder
 * @package Partymeister\Accounting\Database\Seeds
 */
class SlideCategoriesTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Motor\Backend\Models\Category::create([
            'name'       => 'Slides',
            'scope'      => 'slides',
            'created_by' => 1,
            'updated_by' => 1,

            'children' => [
                [
                    'name'       => 'Announcements',
                    'scope'      => 'slides',
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'name'       => 'Timetable',
                    'scope'      => 'slides',
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'name'       => 'Sponsors',
                    'scope'      => 'slides',
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'name'       => 'Party & Demoscene promotions',
                    'scope'      => 'slides',
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
                [
                    'name'       => 'Competitions',
                    'scope'      => 'slides',
                    'created_by' => 1,
                    'updated_by' => 1,
                ],
            ],
        ]);
    }
}
