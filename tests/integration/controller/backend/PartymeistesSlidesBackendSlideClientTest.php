<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Partymeister\Slides\Models\SlideClient;

class PartymeisterSlidesBackendSlideClientTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $readPermission;

    protected $writePermission;

    protected $deletePermission;

    protected $tables = [
        'slide_clients',
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

        $this->readPermission = create_test_permission_with_name('slide_clients.read');
        $this->writePermission = create_test_permission_with_name('slide_clients.write');
        $this->deletePermission = create_test_permission_with_name('slide_clients.delete');

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_see_grid_without_slide_client()
    {
        $this->visit('/backend/slide_clients')
            ->see(trans('partymeister-slides::backend/slide_clients.slide_clients'))
            ->see(trans('motor-backend::backend/global.no_records'));
    }

    /** @test */
    public function can_see_grid_with_one_slide_client()
    {
        $record = create_test_slide_client();
        $this->visit('/backend/slide_clients')
            ->see(trans('partymeister-slides::backend/slide_clients.slide_clients'))
            ->see($record->name);
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_slide_client_and_use_the_back_button()
    {
        $record = create_test_slide_client();
        $this->visit('/backend/slide_clients')
            ->within('table', function () {
                $this->click(trans('motor-backend::backend/global.edit'));
            })
            ->seePageIs('/backend/slide_clients/'.$record->id.'/edit')
            ->click(trans('motor-backend::backend/global.back'))
            ->seePageIs('/backend/slide_clients');
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_slide_client_and_change_values()
    {
        $record = create_test_slide_client();

        $this->visit('/backend/slide_clients/'.$record->id.'/edit')
            ->see($record->name)
            ->type('Updated Slide client', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slide_clients.save'));
            })
            ->see(trans('partymeister-slides::backend/slide_clients.updated'))
            ->see('Updated Slide client')
            ->seePageIs('/backend/slide_clients');

        $record = SlideClient::find($record->id);
        $this->assertEquals('Updated Slide client', $record->name);
    }

    /** @test */
    public function can_click_the_slide_client_create_button()
    {
        $this->visit('/backend/slide_clients')
            ->click(trans('partymeister-slides::backend/slide_clients.new'))
            ->seePageIs('/backend/slide_clients/create');
    }

    /** @test */
    public function can_create_a_new_slide_client()
    {
        $this->visit('/backend/slide_clients/create')
            ->see(trans('partymeister-slides::backend/slide_clients.new'))
            ->type('Create Slide client Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slide_clients.save'));
            })
            ->see(trans('partymeister-slides::backend/slide_clients.created'))
            ->see('Create Slide client Name')
            ->seePageIs('/backend/slide_clients');
    }

    /** @test */
    public function cannot_create_a_new_slide_client_with_empty_fields()
    {
        $this->visit('/backend/slide_clients/create')
            ->see(trans('partymeister-slides::backend/slide_clients.new'))
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slide_clients.save'));
            })
            ->see('Data missing!')
            ->seePageIs('/backend/slide_clients/create');
    }

    /** @test */
    public function can_modify_a_slide_client()
    {
        $record = create_test_slide_client();
        $this->visit('/backend/slide_clients/'.$record->id.'/edit')
            ->see(trans('partymeister-slides::backend/slide_clients.edit'))
            ->type('Modified Slide client Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slide_clients.save'));
            })
            ->see(trans('partymeister-slides::backend/slide_clients.updated'))
            ->see('Modified Slide client Name')
            ->seePageIs('/backend/slide_clients');
    }

    /** @test */
    public function can_delete_a_slide_client()
    {
        create_test_slide_client();

        $this->assertCount(1, SlideClient::all());

        $this->visit('/backend/slide_clients')
            ->within('table', function () {
                $this->press(trans('motor-backend::backend/global.delete'));
            })
            ->seePageIs('/backend/slide_clients')
            ->see(trans('partymeister-slides::backend/slide_clients.deleted'));

        $this->assertCount(0, SlideClient::all());
    }

    /** @test */
    public function can_paginate_slide_client_results()
    {
        $records = create_test_slide_client(100);
        $this->visit('/backend/slide_clients')
            ->within('.pagination', function () {
                $this->click('3');
            })
            ->seePageIs('/backend/slide_clients?page=3');
    }

    /** @test */
    public function can_search_slide_client_results()
    {
        $records = create_test_slide_client(10);
        $this->visit('/backend/slide_clients')
            ->type(substr($records[6]->name, 0, 3), 'search')
            ->press('grid-search-button')
            ->seeInField('search', substr($records[6]->name, 0, 3))
            ->see($records[6]->name);
    }
}
