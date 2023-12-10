<?php

namespace Tests\Feature;

use App\Models\Teacher;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use WithFaker;

    public function authenticateTeacher(): void
    {
        $teacher = Teacher::class::factory()->create();
        $this->actingAs($teacher);
    }

    public function testStoreNewTeacher(): void
    {
        $username = $this->faker->unique()->userName;

        $response = $this->postJson('/api/teacher', [
            'full_name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'username' => $username,
            'password' => $this->faker->password(8, 12),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas(Teacher::class, ['username' => $username]);
    }

    public function testIndexTeachers(): void
    {
        $this->authenticateTeacher();
        $response = $this->getJson('/api/teacher');
        $response->assertOk();
        $response->assertJson([]);
    }

    public function testShowTeacher(): void
    {
        $teacher = Teacher::class::factory()->create();
        $this->authenticateTeacher();
        $response = $this->getJson("/api/teacher/$teacher->id");
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $teacher->id,
            'full_name' => $teacher->full_name,
            'username' => $teacher->username,
            'email' => $teacher->email
        ]);
    }

    public function testUpdateTeacher(): void
    {
        $teacher = Teacher::class::factory()->create();
        $updateData = [
            'full_name' => $this->faker->name(),
            'username' => $this->faker->name(),
            'email' => $this->faker->email
        ];

        $this->authenticateTeacher();
        $response = $this->putJson("/api/teacher/$teacher->id", $updateData);
        $response->assertOk();
        $this->assertDatabaseHas(Teacher::class, [
            'id' => $teacher->id,
            'full_name' => $updateData['full_name'],
            'username' => $updateData['username'],
            'email' => $updateData['email'],
        ]);
    }

    public function testLoginTeacher(): void
    {
        $teacher = Teacher::class::factory()->create([
            'username' => 'teacher',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson("/api/teacher/login", [
            'username' => $teacher->username,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function testDestroyTeacher(): void
    {
        $teacher = Teacher::class::factory()->create();
        $this->authenticateTeacher();
        $response = $this->deleteJson("/api/teacher/$teacher->id");
        $response->assertOk();
        $this->assertDatabaseMissing(Teacher::class, ['id' => $teacher->id]);
    }
}
