<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function authenticateStudent(): void
    {
        $student = Student::class::factory()->create();
        $this->actingAs($student);
    }

    public function testStoreNewStudent(): void
    {
        $username = $this->faker->unique()->userName;

        $response = $this->postJson('/api/student', [
            'full_name' => $this->faker->name(),
            'grade' => $this->faker->numberBetween(0,12),
            'username' => $username,
            'password' => $this->faker->password(8, 12),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas(Student::class, ['username' => $username]);
    }

    public function testIndexStudents(): void
    {
        $this->authenticateStudent();
        $response = $this->getJson('/api/student');
        $response->assertOk();
        $response->assertJson([]);
    }

    public function testShowStudent(): void
    {
        $student = Student::class::factory()->create();
        $this->authenticateStudent();
        $response = $this->getJson("/api/student/$student->id");
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $student->id,
            'username' => $student->username,
            'full_name' => $student->full_name,
            'grade' => $student->grade
        ]);
    }

    public function testUpdateStudent(): void
    {
        $student = Student::class::factory()->create();
        $updateData = [
            'full_name' => $this->faker->name(),
            'username' => $this->faker->name(),
            'grade' => $this->faker->numberBetween(0,12)
        ];

        $this->authenticateStudent();
        $response = $this->putJson("/api/student/$student->id", $updateData);
        $response->assertOk();
        $this->assertDatabaseHas(Student::class, [
            'id' => $student->id,
            'full_name' => $updateData['full_name'],
            'username' => $updateData['username'],
            'grade' => $updateData['grade'],
        ]);
    }

    public function testLoginStudent(): void
    {
        $student = Student::class::factory()->create([
            'username' => 'student',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson("/api/student/login", [
            'username' => $student->username,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function testDestroyStudent(): void
    {
        $student = Student::class::factory()->create();
        $this->authenticateStudent();
        $response = $this->deleteJson("/api/student/$student->id");
        $response->assertOk();
        $this->assertDatabaseMissing(Student::class, ['id' => $student->id]);
    }

    /**
     * Test attaching a student to a period.
     *
     * @return void
     */
    public function testAttachPeriod()
    {
        $this->authenticateStudent();
        $student = Student::factory()->create();
        $period = File::factory()->create();
        $response = $this->post("/api/student/{$student->id}/period/{$period->id}");
        $response->assertOk();
    }

    /**
     * Test detaching a student from a period.
     *
     * @return void
     */
    public function testDetachPeriod()
    {
        $this->authenticateStudent();
        $student = Student::factory()->create();
        $period = File::factory()->create();
        $period->students()->attach($student);
        $response = $this->delete("/api/student/{$student->id}/period/{$period->id}");
        $response->assertOk();
    }
}
