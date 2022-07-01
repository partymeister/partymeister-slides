<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Partymeister\Slides\Models\SlideTemplate;

class PartymeisterSlidesBackendSlideTemplateTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $readPermission;

    protected $writePermission;

    protected $deletePermission;

    protected $tables = [
        'slide_templates',
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

    public function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../../../../database/factories');

        $this->addDefaults();
    }

    protected function addDefaults()
    {
        $this->user = create_test_superadmin();

        $this->readPermission = create_test_permission_with_name('slide_templates.read');
        $this->writePermission = create_test_permission_with_name('slide_templates.write');
        $this->deletePermission = create_test_permission_with_name('slide_templates.delete');

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_see_grid_without_slide_template()
    {
        $this->visit('/backend/slide_templates')
            ->see(trans('partymeister-slides::backend/slide_templates.slide_templates'))
            ->see(trans('motor-backend::backend/global.no_records'));
    }

    /** @test */
    public function can_see_grid_with_one_slide_template()
    {
        $record = create_test_slide_template();
        $this->visit('/backend/slide_templates')
            ->see(trans('partymeister-slides::backend/slide_templates.slide_templates'))
            ->see($record->name);
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_slide_template_and_use_the_back_button()
    {
        $record = create_test_slide_template();
        $this->visit('/backend/slide_templates')
            ->within('table', function () {
                $this->click(trans('motor-backend::backend/global.edit'));
            })
            ->seePageIs('/backend/slide_templates/'.$record->id.'/edit')
            ->click(trans('motor-backend::backend/global.back'))
            ->seePageIs('/backend/slide_templates');
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_slide_template_and_change_values()
    {
        $record = create_test_slide_template();

        $this->visit('/backend/slide_templates/'.$record->id.'/edit')
            ->see($record->name)
            ->type('Updated Slide template', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slide_templates.save'));
            })
            ->see(trans('partymeister-slides::backend/slide_templates.updated'))
            ->see('Updated Slide template')
            ->seePageIs('/backend/slide_templates');

        $record = SlideTemplate::find($record->id);
        $this->assertEquals('Updated Slide template', $record->name);
    }

    /** @test */
    public function can_click_the_slide_template_create_button()
    {
        $this->visit('/backend/slide_templates')
            ->click(trans('partymeister-slides::backend/slide_templates.new'))
            ->seePageIs('/backend/slide_templates/create');
    }

    /** @test */
    public function can_create_a_new_slide_template()
    {
        $this->visit('/backend/slide_templates/create')
            ->see(trans('partymeister-slides::backend/slide_templates.new'))
            ->type('Create Slide template Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slide_templates.save'));
            })
            ->see(trans('partymeister-slides::backend/slide_templates.created'))
            ->see('Create Slide template Name')
            ->seePageIs('/backend/slide_templates');
    }

    /** @test */
    public function cannot_create_a_new_slide_template_with_empty_fields()
    {
        $this->visit('/backend/slide_templates/create')
            ->see(trans('partymeister-slides::backend/slide_templates.new'))
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slide_templates.save'));
            })
            ->see('Data missing!')
            ->seePageIs('/backend/slide_templates/create');
    }

    /** @test */
    public function can_modify_a_slide_template()
    {
        $record = create_test_slide_template();
        $this->visit('/backend/slide_templates/'.$record->id.'/edit')
            ->see(trans('partymeister-slides::backend/slide_templates.edit'))
            ->type('Modified Slide template Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/slide_templates.save'));
            })
            ->see(trans('partymeister-slides::backend/slide_templates.updated'))
            ->see('Modified Slide template Name')
            ->seePageIs('/backend/slide_templates');
    }

    /** @test */
    public function can_delete_a_slide_template()
    {
        create_test_slide_template();

        $this->assertCount(1, SlideTemplate::all());

        $this->visit('/backend/slide_templates')
            ->within('table', function () {
                $this->press(trans('motor-backend::backend/global.delete'));
            })
            ->seePageIs('/backend/slide_templates')
            ->see(trans('partymeister-slides::backend/slide_templates.deleted'));

        $this->assertCount(0, SlideTemplate::all());
    }

    /** @test */
    public function can_paginate_slide_template_results()
    {
        $records = create_test_slide_template(100);
        $this->visit('/backend/slide_templates')
            ->within('.pagination', function () {
                $this->click('3');
            })
            ->seePageIs('/backend/slide_templates?page=3');
    }

    /** @test */
    public function can_search_slide_template_results()
    {
        $records = create_test_slide_template(10);
        $this->visit('/backend/slide_templates')
            ->type(substr($records[6]->name, 0, 3), 'search')
            ->press('grid-search-button')
            ->seeInField('search', substr($records[6]->name, 0, 3))
            ->see($records[6]->name);
    }
}
