<?php

namespace Partymeister\Slides\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Class AccountsTableSeeder
 */
class PartymeisterSlidesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(
            [
                SlideCategoriesTableSeeder::class,
                TransitionsTableSeeder::class,
                SlideClientsTableSeeder::class,
                SlideTemplatesTableSeeder::class,
                SlidesTableSeeder::class,
                PlaylistsTableSeeder::class,
            ]
        );
    }
}
