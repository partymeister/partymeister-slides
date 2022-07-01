<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class PartymeisterSlidesApiSlideClientTest extends TestCase
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

    public function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../../../../database/factories');

        $this->addDefaults();
    }

    protected function addDefaults()
    {
        $this->user = create_test_user();
        $this->readPermission = create_test_permission_with_name('slide_clients.read');
        $this->writePermission = create_test_permission_with_name('slide_clients.write');
        $this->deletePermission = create_test_permission_with_name('slide_clients.delete');
    }

    /**
     * @test
     */
    public function returns_403_for_slide_client_if_not_authenticated()
    {
        $this->json('GET', '/api/slide_clients/1')->seeStatusCode(401)->seeJson(['error' => 'Unauthenticated.']);
    }

    /** @test */
    public function returns_404_for_non_existing_slide_client_record()
    {
        $this->user->givePermissionTo($this->readPermission);
        $this->json('GET', '/api/slide_clients/1?api_token='.$this->user->api_token)->seeStatusCode(404)->seeJson([
            'message' => 'Record not found',
        ]);
    }

    /** @test */
    public function fails_if_trying_to_create_slide_client_without_payload()
    {
        $this->user->givePermissionTo($this->writePermission);
        $this->json('POST', '/api/slide_clients?api_token='.$this->user->api_token)->seeStatusCode(422)->seeJson([
            'name' => ['The name field is required.'],
        ]);
    }

    /** @test */
    public function fails_if_trying_to_create_slide_client_without_permission()
    {
        $this->json('POST', '/api/slide_clients?api_token='.$this->user->api_token)->seeStatusCode(403)->seeJson([
            'error' => 'Access denied.',
        ]);
    }

    /** @test */
    public function can_create_a_new_slide_client()
    {
        $this->user->givePermissionTo($this->writePermission);
        $this->json('POST', '/api/slide_clients?api_token='.$this->user->api_token, [
            'name' => 'Test Slide client',
        ])->seeStatusCode(200)->seeJson([
            'name' => 'Test Slide client',
        ]);
    }

    /** @test */
    public function can_show_a_single_slide_client()
    {
        $this->user->givePermissionTo($this->readPermission);
        $record = create_test_slide_client();
        $this->json(
            'GET',
            '/api/slide_clients/'.$record->id.'?api_token='.$this->user->api_token
        )->seeStatusCode(200)->seeJson([
            'name' => $record->name,
        ]);
    }

    /** @test */
    public function fails_to_show_a_single_slide_client_without_permission()
    {
        $record = create_test_slide_client();
        $this->json(
            'GET',
            '/api/slide_clients/'.$record->id.'?api_token='.$this->user->api_token
        )->seeStatusCode(403)->seeJson([
            'error' => 'Access denied.',
        ]);
    }

    /** @test */
    public function can_get_empty_result_when_trying_to_show_multiple_slide_client()
    {
        $this->user->givePermissionTo($this->readPermission);
        $this->json('GET', '/api/slide_clients?api_token='.$this->user->api_token)->seeStatusCode(200)->seeJson([
            'total' => 0,
        ]);
    }

    /** @test */
    public function can_show_multiple_slide_client()
    {
        $this->user->givePermissionTo($this->readPermission);
        $records = create_test_slide_client(10);
        $this->json('GET', '/api/slide_clients?api_token='.$this->user->api_token)->seeStatusCode(200)->seeJson([
            'name' => $records[0]->name,
        ]);
    }

    /** @test */
    public function can_search_for_a_slide_client()
    {
        $this->user->givePermissionTo($this->readPermission);
        $records = create_test_slide_client(10);
        $this->json(
            'GET',
            '/api/slide_clients?api_token='.$this->user->api_token.'&search='.$records[2]->name
        )->seeStatusCode(200)->seeJson([
            'name' => $records[2]->name,
        ]);
    }

    /** @test */
    public function can_show_a_second_slide_client_results_page()
    {
        $this->user->givePermissionTo($this->readPermission);
        create_test_slide_client(50);
        $this->json(
            'GET',
            '/api/slide_clients?api_token='.$this->user->api_token.'&page=2'
        )->seeStatusCode(200)->seeJson([
            'current_page' => 2,
        ]);
    }

    /** @test */
    public function fails_if_trying_to_update_nonexisting_slide_client()
    {
        $this->user->givePermissionTo($this->writePermission);
        $this->json('PATCH', '/api/slide_clients/2?api_token='.$this->user->api_token)->seeStatusCode(404)->seeJson([
            'message' => 'Record not found',
        ]);
    }

    /** @test */
    public function fails_if_trying_to_modify_a_slide_client_without_payload()
    {
        $this->user->givePermissionTo($this->writePermission);
        $record = create_test_slide_client();
        $this->json(
            'PATCH',
            '/api/slide_clients/'.$record->id.'?api_token='.$this->user->api_token
        )->seeStatusCode(422)->seeJson([
            'name' => ['The name field is required.'],
        ]);
    }

    /** @test */
    public function fails_if_trying_to_modify_a_slide_client_without_permission()
    {
        $record = create_test_slide_client();
        $this->json(
            'PATCH',
            '/api/slide_clients/'.$record->id.'?api_token='.$this->user->api_token
        )->seeStatusCode(403)->seeJson([
            'error' => 'Access denied.',
        ]);
    }

    /** @test */
    public function can_modify_a_slide_client()
    {
        $this->user->givePermissionTo($this->writePermission);
        $record = create_test_slide_client();
        $this->json('PATCH', '/api/slide_clients/'.$record->id.'?api_token='.$this->user->api_token, [
            'name' => 'Modified Slide client',
        ])->seeStatusCode(200)->seeJson([
            'name' => 'Modified Slide client',
        ]);
    }

    /** @test */
    public function fails_if_trying_to_delete_a_non_existing_slide_client()
    {
        $this->user->givePermissionTo($this->deletePermission);
        $this->json('DELETE', '/api/slide_clients/1?api_token='.$this->user->api_token)->seeStatusCode(404)->seeJson([
            'message' => 'Record not found',
        ]);
    }

    /** @test */
    public function fails_to_delete_a_slide_client_without_permission()
    {
        $record = create_test_slide_client();
        $this->json(
            'DELETE',
            '/api/slide_clients/'.$record->id.'?api_token='.$this->user->api_token
        )->seeStatusCode(403)->seeJson([
            'error' => 'Access denied.',
        ]);
    }

    /** @test */
    public function can_delete_a_slide_client()
    {
        $this->user->givePermissionTo($this->deletePermission);
        $record = create_test_slide_client();
        $this->json(
            'DELETE',
            '/api/slide_clients/'.$record->id.'?api_token='.$this->user->api_token
        )->seeStatusCode(200)->seeJson([
            'success' => true,
        ]);
    }
}
