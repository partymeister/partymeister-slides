<?php

use Motor\Admin\Models\User;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Models\SlideTemplate;
use Spatie\Permission\Models\Role;

pest()->group('V2', 'Slide');

beforeEach(function () {
    $role = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $user = User::factory()->create(['email' => 'admin@motor-cms.com', 'name' => 'Admin']);
    $user->assignRole($role);

    $template = SlideTemplate::create(['name' => 'Compo', 'template_for' => 'competition', 'definitions' => '{}']);
    Slide::create(['name' => 'Welcome', 'slide_type' => 'default', 'slide_template_id' => $template->id, 'definitions' => '{}']);
    Slide::create(['name' => 'Goodbye', 'slide_type' => 'default', 'definitions' => '{}']);
});

describe('V2 Slides API', function () {
    it('includes api_version v2 in response meta', function () {
        $response = $this->asAdmin()->getJson('/api/v2/slides');
        $response->assertStatus(200)->assertJsonPath('meta.api_version', 'v2');
    });

    it('can get all slides', function () {
        assertV2CrudIndex('/api/v2/slides', 2, ['id', 'name', 'slide_type', 'definitions']);
    });

    it('can get a specific slide with template relationship', function () {
        $slide = Slide::whereNotNull('slide_template_id')->first();
        $response = $this->asAdmin()->getJson('/api/v2/slides/'.$slide->id);
        $response->assertStatus(200)
            ->assertJsonPath('meta.api_version', 'v2')
            ->assertJsonStructure(['data' => ['id', 'name', 'slide_template']]);
    });

    it('can create a slide', function () {
        assertV2CrudCreate('/api/v2/slides', [
            'name' => 'Test Slide',
            'slide_type' => 'default',
            'definitions' => '{}',
        ], Slide::class);
    });

    it('validates required fields on create', function () {
        $countBefore = Slide::count();
        $this->asAdmin()->postJson('/api/v2/slides', [])->assertStatus(422);
        expect(Slide::count())->toBe($countBefore);
    });

    it('can update a slide', function () {
        assertV2CrudUpdate(
            '/api/v2/slides/'.Slide::first()->id,
            ['name' => 'Updated Slide'],
            'name',
            'Updated Slide'
        );
    });

    it('can delete a slide with 204 No Content', function () {
        assertV2CrudDelete('/api/v2/slides/'.Slide::latest('id')->first()->id, Slide::class);
    });
});
