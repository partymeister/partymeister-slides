<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Partymeister\Slides\Models\Playlist;

class PartymeisterSlidesBackendPlaylistTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $readPermission;

    protected $writePermission;

    protected $deletePermission;

    protected $tables = [
        'playlists',
        'users',
        'languages',
        'clients',
        'permissions',
        'roles',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'media',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../../../../database/factories');

        $this->addDefaults();
    }

    protected function addDefaults()
    {
        $this->user = create_test_superadmin();

        $this->readPermission = create_test_permission_with_name('playlists.read');
        $this->writePermission = create_test_permission_with_name('playlists.write');
        $this->deletePermission = create_test_permission_with_name('playlists.delete');

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_see_grid_without_playlist()
    {
        $this->visit('/backend/playlists')
            ->see(trans('partymeister-slides::backend/playlists.playlists'))
            ->see(trans('motor-backend::backend/global.no_records'));
    }

    /** @test */
    public function can_see_grid_with_one_playlist()
    {
        $record = create_test_playlist();
        $this->visit('/backend/playlists')
            ->see(trans('partymeister-slides::backend/playlists.playlists'))
            ->see($record->name);
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_playlist_and_use_the_back_button()
    {
        $record = create_test_playlist();
        $this->visit('/backend/playlists')
            ->within('table', function () {
                $this->click(trans('motor-backend::backend/global.edit'));
            })
            ->seePageIs('/backend/playlists/'.$record->id.'/edit')
            ->click(trans('motor-backend::backend/global.back'))
            ->seePageIs('/backend/playlists');
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_playlist_and_change_values()
    {
        $record = create_test_playlist();

        $this->visit('/backend/playlists/'.$record->id.'/edit')
            ->see($record->name)
            ->type('Updated Playlist', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/playlists.save'));
            })
            ->see(trans('partymeister-slides::backend/playlists.updated'))
            ->see('Updated Playlist')
            ->seePageIs('/backend/playlists');

        $record = Playlist::find($record->id);
        $this->assertEquals('Updated Playlist', $record->name);
    }

    /** @test */
    public function can_click_the_playlist_create_button()
    {
        $this->visit('/backend/playlists')
            ->click(trans('partymeister-slides::backend/playlists.new'))
            ->seePageIs('/backend/playlists/create');
    }

    /** @test */
    public function can_create_a_new_playlist()
    {
        $this->visit('/backend/playlists/create')
            ->see(trans('partymeister-slides::backend/playlists.new'))
            ->type('Create Playlist Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/playlists.save'));
            })
            ->see(trans('partymeister-slides::backend/playlists.created'))
            ->see('Create Playlist Name')
            ->seePageIs('/backend/playlists');
    }

    /** @test */
    public function cannot_create_a_new_playlist_with_empty_fields()
    {
        $this->visit('/backend/playlists/create')
            ->see(trans('partymeister-slides::backend/playlists.new'))
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/playlists.save'));
            })
            ->see('Data missing!')
            ->seePageIs('/backend/playlists/create');
    }

    /** @test */
    public function can_modify_a_playlist()
    {
        $record = create_test_playlist();
        $this->visit('/backend/playlists/'.$record->id.'/edit')
            ->see(trans('partymeister-slides::backend/playlists.edit'))
            ->type('Modified Playlist Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/playlists.save'));
            })
            ->see(trans('partymeister-slides::backend/playlists.updated'))
            ->see('Modified Playlist Name')
            ->seePageIs('/backend/playlists');
    }

    /** @test */
    public function can_delete_a_playlist()
    {
        create_test_playlist();

        $this->assertCount(1, Playlist::all());

        $this->visit('/backend/playlists')
            ->within('table', function () {
                $this->press(trans('motor-backend::backend/global.delete'));
            })
            ->seePageIs('/backend/playlists')
            ->see(trans('partymeister-slides::backend/playlists.deleted'));

        $this->assertCount(0, Playlist::all());
    }

    /** @test */
    public function can_paginate_playlist_results()
    {
        $records = create_test_playlist(100);
        $this->visit('/backend/playlists')
            ->within('.pagination', function () {
                $this->click('3');
            })
            ->seePageIs('/backend/playlists?page=3');
    }

    /** @test */
    public function can_search_playlist_results()
    {
        $records = create_test_playlist(10);
        $this->visit('/backend/playlists')
            ->type(substr($records[6]->name, 0, 3), 'search')
            ->press('grid-search-button')
            ->seeInField('search', substr($records[6]->name, 0, 3))
            ->see($records[6]->name);
    }
}
