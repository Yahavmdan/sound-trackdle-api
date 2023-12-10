<?php


use App\Models\Period;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PeriodTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function authenticateTeacher(): void
    {
        $teacher = Teacher::class::factory()->create();
        $this->actingAs($teacher);
    }

    public function testStoreNewPeriod(): void
    {
        $this->authenticateTeacher();
        $name = $this->faker->name;

        $response = $this->postJson('/api/period', [
            'name' => $name,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas(Period::class, ['name' => $name]);
    }

    public function testIndexPeriod(): void
    {
        $this->authenticateTeacher();
        $response = $this->getJson('/api/period');
        $response->assertOk();
        $response->assertJson([]);
    }

    public function testShowPeriod(): void
    {
        $period = Period::class::factory()->create();
        $this->authenticateTeacher();
        $response = $this->getJson("/api/period/$period->id");
        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $period->name,
        ]);
    }

    public function testUpdatePeriod(): void
    {
        $period = Period::class::factory()->create();
        $updateData = [
            'name' => $this->faker->name(),
        ];

        $this->authenticateTeacher();
        $response = $this->putJson("/api/period/$period->id", $updateData);
        $response->assertOk();
        $this->assertDatabaseHas(Period::class, [
            'name' => $updateData['name'],
        ]);
    }

    public function testDestroyPeriod(): void
    {
        $period = Period::class::factory()->create();
        $this->authenticateTeacher();
        $response = $this->deleteJson("/api/period/$period->id");
        $response->assertOk();
        $this->assertDatabaseMissing(Period::class, ['id' => $period->id]);
    }
}
