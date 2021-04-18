<?php

namespace Partymeister\Slides\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Partymeister\Competitions\Models\OptionGroup;
use Partymeister\Core\Models\User;

/**
 * Class AccountsTableSeeder
 * @package Partymeister\Accounting\Database\Seeds
 */
class SlideTemplatesTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $slideTemplates = [
            [
                'name'         => 'Announce',
                'template_for' => 'basic',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/announce.json')
            ],
            [
                'name'         => 'Announce with image on the right',
                'template_for' => 'basic',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/announce_with_image_right.json')
            ],
            [
                'name'         => 'Coming up',
                'template_for' => 'coming_up',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/coming_up.json')
            ],
            [
                'name'         => 'Now up',
                'template_for' => 'now',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/now.json')
            ],
            [
                'name'         => 'End',
                'template_for' => 'end',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/end.json')
            ],
            [
                'name'         => 'End of Prizegiving',
                'template_for' => 'end_of_pg',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/prizegiving_end.json')
            ],
            [
                'name'         => 'Competition Entry',
                'template_for' => 'competition',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/competition_entry-2-n.json')
            ],
            [
                'name'         => 'Competition Entry (first entry without previous info)',
                'template_for' => 'competition_entry_1',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/competition_entry-1.json')
            ],
            [
                'name'         => 'Timetable',
                'template_for' => 'timetable',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/timetable.json')
            ],
            [
                'name'         => 'Entry comments',
                'template_for' => 'comments',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/comments.json')
            ],
            [
                'name'         => 'Prizegiving',
                'template_for' => 'prizegiving',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/prizegiving.json')
            ],
            [
                'name'         => 'Participants',
                'template_for' => 'participants',
                'definitions'  => file_get_contents(__DIR__.'/slide_templates/participants.json')
            ],
        ];

        foreach ($slideTemplates as $slideTemplate) {
            DB::table('slide_templates')->insert([
                'name'         => $slideTemplate[ 'name' ],
                'template_for' => $slideTemplate[ 'template_for' ],
                'definitions'  => $slideTemplate[ 'definitions' ],
                'created_by'   => User::get()->first()->id,
                'updated_by'   => User::get()->first()->id,
            ]);
        }
    }
}
