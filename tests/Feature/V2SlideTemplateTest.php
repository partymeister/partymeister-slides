<?php

use Motor\Admin\Models\User;
use Partymeister\Slides\Models\SlideTemplate;
use Spatie\Permission\Models\Role;

pest()->group('V2', 'SlideTemplate');

beforeEach(function () {
    $role = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $user = User::factory()->create(['email' => 'admin@motor-cms.com', 'name' => 'Admin']);
    $user->assignRole($role);

    SlideTemplate::factory()->create(['name' => 'Compo', 'template_for' => 'competition', 'definitions' => '{}', 'cached_html_preview' => '', 'cached_html_final' => '']);
    SlideTemplate::factory()->create(['name' => 'Event', 'template_for' => 'event', 'definitions' => '{}', 'cached_html_preview' => '', 'cached_html_final' => '']);
});

describe('V2 SlideTemplates API', function () {
    it('requires authentication', function () {
        assertV2RequiresAuth('/api/v2/slide-templates');
    });

    it('includes api_version v2 in response meta', function () {
        $response = $this->asAdmin()->getJson('/api/v2/slide-templates');
        $response->assertStatus(200)->assertJsonPath('meta.api_version', 'v2');
    });

    it('can get all slide templates', function () {
        assertV2CrudIndex('/api/v2/slide-templates', 2, ['id', 'name', 'template_for', 'definitions']);
    });

    it('can get a specific slide template', function () {
        assertV2CrudShow('/api/v2/slide-templates/'.SlideTemplate::first()->id, ['id', 'name', 'template_for']);
    });

    it('can create a slide template', function () {
        assertV2CrudCreate('/api/v2/slide-templates', [
            'name' => 'Prize',
            'template_for' => 'prizegiving',
            'definitions' => '{}',
        ], SlideTemplate::class);
    });

    it('validates required fields on create', function () {
        $countBefore = SlideTemplate::count();
        $this->asAdmin()->postJson('/api/v2/slide-templates', [])->assertStatus(422);
        expect(SlideTemplate::count())->toBe($countBefore);
    });

    it('can update a slide template', function () {
        assertV2CrudUpdate(
            '/api/v2/slide-templates/'.SlideTemplate::first()->id,
            ['name' => 'Updated Compo'],
            'name',
            'Updated Compo'
        );
    });

    it('can delete a slide template with 204 No Content', function () {
        assertV2CrudDelete('/api/v2/slide-templates/'.SlideTemplate::latest('id')->first()->id, SlideTemplate::class);
    });
});
