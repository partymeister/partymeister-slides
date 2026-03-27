<?php

use Motor\Admin\Models\User;
use Partymeister\Slides\Models\Transition;
use Spatie\Permission\Models\Role;

pest()->group('V2', 'Transition');

beforeEach(function () {
    $role = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $user = User::factory()->create(['email' => 'admin@motor-cms.com', 'name' => 'Admin']);
    $user->assignRole($role);

    Transition::create(['name' => 'Fade', 'client_type' => 'slidemeister-web', 'identifier' => 'fade', 'default_duration' => 500]);
    Transition::create(['name' => 'Cut', 'client_type' => 'slidemeister-web', 'identifier' => 'cut', 'default_duration' => 0]);
});

describe('V2 Transitions API', function () {
    it('includes api_version v2 in response meta', function () {
        $response = $this->asAdmin()->getJson('/api/v2/transitions');
        $response->assertStatus(200)->assertJsonPath('meta.api_version', 'v2');
    });

    it('can get all transitions', function () {
        assertV2CrudIndex('/api/v2/transitions', 2, ['id', 'name', 'client_type', 'identifier', 'default_duration']);
    });

    it('can get a specific transition', function () {
        assertV2CrudShow('/api/v2/transitions/'.Transition::first()->id, ['id', 'name', 'client_type', 'identifier']);
    });

    it('can create a transition', function () {
        assertV2CrudCreate('/api/v2/transitions', [
            'name' => 'Dissolve',
            'client_type' => 'slidemeister-web',
            'identifier' => 'dissolve',
            'default_duration' => 300,
        ], Transition::class);
    });

    it('validates required fields on create', function () {
        $countBefore = Transition::count();
        $this->asAdmin()->postJson('/api/v2/transitions', [])->assertStatus(422);
        expect(Transition::count())->toBe($countBefore);
    });

    it('can update a transition', function () {
        assertV2CrudUpdate(
            '/api/v2/transitions/'.Transition::first()->id,
            ['name' => 'Updated Fade'],
            'name',
            'Updated Fade'
        );
    });

    it('can delete a transition with 204 No Content', function () {
        assertV2CrudDelete('/api/v2/transitions/'.Transition::latest('id')->first()->id, Transition::class);
    });
});
