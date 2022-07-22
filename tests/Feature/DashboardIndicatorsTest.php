<?php

namespace Tests\Feature;

use App\Enums\ClientUserStatus;
use App\Models\Client;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardIndicatorsTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->seed();

        $user = User::factory()
            ->hasAttached(Client::factory()->count(1),
                ['status' => ClientUserStatus::Asignado])
            ->create();

        $client = $user->assigned_client->first();

        $operations = Operation::factory()
            ->count(12)
            ->state([
                'amount' => 2000.0
            ])->state(new Sequence(
                ['operation_status_id' => 1],
                ['operation_status_id' => 4],
                ['operation_status_id' => 5]
            ))->create();

        $client->operations()->saveMany($operations);

        $response = $this->actingAs($user)->get("api/dashboard/indicators?client_id=$client->id");

        $response->assertStatus(200);

        $response->assertJson([
            'success' => true
        ]);

        $data = $response->getData();

        self::assertTrue(count($data->data->operations) == 5, "No se obtuvieron 5 operacion");

        self::assertNotNull($data->data->total_operated_amount, "Monto total no valido");

        self::assertTrue($data->data->total_operated_amount == 2000.0 * 8, "Monto total incorrecto");

    }
}
