<?php

namespace Tests\Feature;

use App\Enums\ClientUserStatus;
use App\Enums\UserStatus;
use App\Models\Client;
use App\Models\User;
use Database\Factories\ClientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $user = User::factory()
            ->active()
            ->hasAttached(
                Client::factory()->count(1),
                ['status' => ClientUserStatus::Asignado])
            ->create();

        $response = $this->post('api/login', ['email' => $user->email, 'password' => 'password']);


        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        self::assertNotNull($response->getData()->data->user, "User was not returned");
        self::assertNotNull($response->getData()->data->assigned_client, "User client has not being assigned");
    }
}
