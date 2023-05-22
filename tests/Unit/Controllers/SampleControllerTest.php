<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

/**
 * Class SampleControllerTest
 * @package Tests\Unit\Controllers
 * @coversDefaultClass \App\Http\Controllers\SampleController
 */
class SampleControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @covers ::index
     */
    public function testIndex()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('login');
    }

    /**
     * @test
     * @covers ::registration
     */
    public function testRegistration()
    {
        $response = $this->get('/registration');

        $response->assertStatus(200);
        $response->assertViewIs('registration');
    }

    /**
     * @test
     * @covers ::validate_registration
     */
    public function testValidateRegistration()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/validate-registration', $userData);

        $response->assertRedirect('login');
        $response->assertSessionHas('success', 'Registration Completed, now you can login');

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    /**
     * @test
     * @covers ::validate_login
     */
    public function testValidateLogin()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/validate-login', $loginData);

        $response->assertRedirect('dashboard');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * @test
     * @covers ::dashboard
     */
    public function testDashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    /**
     * @test
     * @covers ::logout
     */
    public function testLogout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('login');
        $this->assertGuest();
    }

    /**
     * @test
     * @covers ::profile
     */
    public function testProfile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertViewIs('profile');
    }

    /**
     * @test
     * @covers ::profile_validation
     */
    public function testValidateProfile()
    {
        Storage::fake('images');

        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('profile.jpg');

        $profileData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'user_image' => $image,
        ];

        $response = $this->actingAs($user)->post('/validate-profile', $profileData);

        $response->assertRedirect('profile');
        $response->assertSessionHas('success', 'Profile Details Updated');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        Storage::disk('images')->assertExists($user->user_image);
    }
}

