<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Partymeister\Slides\Models\Transition;

class PartymeisterSlidesBackendTransitionTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $readPermission;

    protected $writePermission;

    protected $deletePermission;

    protected $tables = [
        'transitions',
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

        $this->readPermission = create_test_permission_with_name('transitions.read');
        $this->writePermission = create_test_permission_with_name('transitions.write');
        $this->deletePermission = create_test_permission_with_name('transitions.delete');

        $this->actingAs($this->user);
    }

    /** @test */
    public function can_see_grid_without_transition()
    {
        $this->visit('/backend/transitions')
            ->see(trans('partymeister-slides::backend/transitions.transitions'))
            ->see(trans('motor-backend::backend/global.no_records'));
    }

    /** @test */
    public function can_see_grid_with_one_transition()
    {
        $record = create_test_transition();
        $this->visit('/backend/transitions')
            ->see(trans('partymeister-slides::backend/transitions.transitions'))
            ->see($record->name);
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_transition_and_use_the_back_button()
    {
        $record = create_test_transition();
        $this->visit('/backend/transitions')
            ->within('table', function () {
                $this->click(trans('motor-backend::backend/global.edit'));
            })
            ->seePageIs('/backend/transitions/'.$record->id.'/edit')
            ->click(trans('motor-backend::backend/global.back'))
            ->seePageIs('/backend/transitions');
    }

    /** @test */
    public function can_visit_the_edit_form_of_a_transition_and_change_values()
    {
        $record = create_test_transition();

        $this->visit('/backend/transitions/'.$record->id.'/edit')
            ->see($record->name)
            ->type('Updated Transition', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/transitions.save'));
            })
            ->see(trans('partymeister-slides::backend/transitions.updated'))
            ->see('Updated Transition')
            ->seePageIs('/backend/transitions');

        $record = Transition::find($record->id);
        $this->assertEquals('Updated Transition', $record->name);
    }

    /** @test */
    public function can_click_the_transition_create_button()
    {
        $this->visit('/backend/transitions')
            ->click(trans('partymeister-slides::backend/transitions.new'))
            ->seePageIs('/backend/transitions/create');
    }

    /** @test */
    public function can_create_a_new_transition()
    {
        $this->visit('/backend/transitions/create')
            ->see(trans('partymeister-slides::backend/transitions.new'))
            ->type('Create Transition Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/transitions.save'));
            })
            ->see(trans('partymeister-slides::backend/transitions.created'))
            ->see('Create Transition Name')
            ->seePageIs('/backend/transitions');
    }

    /** @test */
    public function cannot_create_a_new_transition_with_empty_fields()
    {
        $this->visit('/backend/transitions/create')
            ->see(trans('partymeister-slides::backend/transitions.new'))
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/transitions.save'));
            })
            ->see('Data missing!')
            ->seePageIs('/backend/transitions/create');
    }

    /** @test */
    public function can_modify_a_transition()
    {
        $record = create_test_transition();
        $this->visit('/backend/transitions/'.$record->id.'/edit')
            ->see(trans('partymeister-slides::backend/transitions.edit'))
            ->type('Modified Transition Name', 'name')
            ->within('.box-footer', function () {
                $this->press(trans('partymeister-slides::backend/transitions.save'));
            })
            ->see(trans('partymeister-slides::backend/transitions.updated'))
            ->see('Modified Transition Name')
            ->seePageIs('/backend/transitions');
    }

    /** @test */
    public function can_delete_a_transition()
    {
        create_test_transition();

        $this->assertCount(1, Transition::all());

        $this->visit('/backend/transitions')
            ->within('table', function () {
                $this->press(trans('motor-backend::backend/global.delete'));
            })
            ->seePageIs('/backend/transitions')
            ->see(trans('partymeister-slides::backend/transitions.deleted'));

        $this->assertCount(0, Transition::all());
    }

    /** @test */
    public function can_paginate_transition_results()
    {
        $records = create_test_transition(100);
        $this->visit('/backend/transitions')
            ->within('.pagination', function () {
                $this->click('3');
            })
            ->seePageIs('/backend/transitions?page=3');
    }

    /** @test */
    public function can_search_transition_results()
    {
        $records = create_test_transition(10);
        $this->visit('/backend/transitions')
            ->type(substr($records[6]->name, 0, 3), 'search')
            ->press('grid-search-button')
            ->seeInField('search', substr($records[6]->name, 0, 3))
            ->see($records[6]->name);
    }
}
