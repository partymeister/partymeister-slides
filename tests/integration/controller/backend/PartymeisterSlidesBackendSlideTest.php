<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Partymeister\Slides\Models\Slide;

class PartymeisterSlidesBackendSlideTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $readPermission;

    protected $writePermission;

    protected $deletePermission;

    protected $tables = [
        'slides',
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

        $this->readPermission = create_test_permission_with_name('slides.read');
        $this->writePermission = create_test_permission_with_name('slides.write');
        $this->deletePermission = create_test_permission_with_name('slides.delete');

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_see_grid_without_slide()
    {
        $this->visit('/backend/slides')
            ->see(trans('partymeister-slides::backend/slides.slides'))
            ->see(trans('motor-backend::backend/global.no_records'));
    }

    /** @test */
    public function can_see_grid_with_one_slide()
    {
        $record = create_test_slide();
        $this->visit('/backend/slides')
            ->see(trans('partymeister-slides::backend/slides.slides'))
            ->see($record->name);
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_slide_and_use_the_back_button()
    {
        $record = create_test_slide();
        $this->visit('/backend/slides')
            ->within('table', function () {
                $this->click(trans('motor-backend::backend/global.edit'));
            })
            ->seePageIs('/backend/slides/'.$record->id.'/edit')
            ->click(trans('motor-backend::backend/global.back'))
            ->seePageIs('/backend/slides');
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_slide_and_change_values()
    {
        $record = create_test_slide();

        $this->visit('/backend/slides/'.$record->id.'/edit')
            ->see($record->name)
            ->type('Updated Slide', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slides.save'));
            })
            ->see(trans('partymeister-slides::backend/slides.updated'))
            ->see('Updated Slide')
            ->seePageIs('/backend/slides');

        $record = Slide::find($record->id);
        $this->assertEquals('Updated Slide', $record->name);
    }

    /** @test */
    public function can_click_the_slide_create_button()
    {
        $this->visit('/backend/slides')
            ->click(trans('partymeister-slides::backend/slides.new'))
            ->seePageIs('/backend/slides/create');
    }

    /** @test */
    public function can_create_a_new_slide()
    {
        $this->visit('/backend/slides/create')
            ->see(trans('partymeister-slides::backend/slides.new'))
            ->type('Create Slide Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slides.save'));
            })
            ->see(trans('partymeister-slides::backend/slides.created'))
            ->see('Create Slide Name')
            ->seePageIs('/backend/slides');
    }

    /** @test */
    public function cannot_create_a_new_slide_with_empty_fields()
    {
        $this->visit('/backend/slides/create')
            ->see(trans('partymeister-slides::backend/slides.new'))
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slides.save'));
            })
            ->see('Data missing!')
            ->seePageIs('/backend/slides/create');
    }

    /** @test */
    public function can_modify_a_slide()
    {
        $record = create_test_slide();
        $this->visit('/backend/slides/'.$record->id.'/edit')
            ->see(trans('partymeister-slides::backend/slides.edit'))
            ->type('Modified Slide Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slides.save'));
            })
            ->see(trans('partymeister-slides::backend/slides.updated'))
            ->see('Modified Slide Name')
            ->seePageIs('/backend/slides');
    }

    /** @test */
    public function can_delete_a_slide()
    {
        create_test_slide();

        $this->assertCount(1, Slide::all());

        $this->visit('/backend/slides')
            ->within('table', function () {
                $this->press(trans('motor-backend::backend/global.delete'));
            })
            ->seePageIs('/backend/slides')
            ->see(trans('partymeister-slides::backend/slides.deleted'));

        $this->assertCount(0, Slide::all());
    }

    /** @test */
    public function can_paginate_slide_results()
    {
        $records = create_test_slide(100);
        $this->visit('/backend/slides')
            ->within('.pagination', function () {
                $this->click('3');
            })
            ->seePageIs('/backend/slides?page=3');
    }

    /** @test */
    public function can_search_slide_results()
    {
        $records = create_test_slide(10);
        $this->visit('/backend/slides')
            ->type(substr($records[6]->name, 0, 3), 'search')
            ->press('grid-search-button')
            ->seeInField('search', substr($records[6]->name, 0, 3))
            ->see($records[6]->name);
    }
}
