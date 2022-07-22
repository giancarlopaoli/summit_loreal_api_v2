<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetMinimumAmountTest extends TestCase
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
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('api/immediate_operation/minimum_amount');

        $response->assertStatus(200);

        $response->assertJson([
            'success'=> true
        ]);

        self::assertNotNull($response->getData()->data->value, "Value not set");

        Configuration::where("shortname", "MNTMIN")->delete();

        $response = $this->actingAs($user)->get('api/immediate_operation/minimum_amount');

        $response->assertStatus(200);

        $response->assertJson([
            'success'=> false
        ]);
    }
}
