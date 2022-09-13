<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\SampleController;
use Tests\TestCase;

/**
 * Class SampleControllerTest.
 *
 * @covers \App\Http\Controllers\SampleController
 */
final class SampleControllerTest extends TestCase
{
    private SampleController $sampleController;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @todo Correctly instantiate tested object to use it. */
        $this->sampleController = new SampleController();
        $this->app->instance(SampleController::class, $this->sampleController);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sampleController);
    }

    public function testIndex(): void
    {
        /** @todo This test is incomplete. */
        $this->get('/')
            ->assertStatus(200);
    }

    public function testRegistration(): void
    {
        /** @todo This test is incomplete. */
        $this->get('/registration')
            ->assertStatus(200);
    }

    public function testValidate_registration(): void
    {
        /** @todo This test is incomplete. */
        $this->get('/validate_registration')
            ->assertStatus(200);
    }

    public function testValidate_login(): void
    {
        /** @todo This test is incomplete. */
        $this->get('/validate_login')
            ->assertStatus(200);
    }

    public function testDashboard(): void
    {
        /** @todo This test is incomplete. */
        $this->get('/dashboard')
            ->assertStatus(200);
    }

    public function testLogout(): void
    {
        /** @todo This test is incomplete. */
        $this->get('/logout')
            ->assertStatus(200);
    }

    public function testProfile(): void
    {
        /** @todo This test is incomplete. */
        $this->get('/profile')
            ->assertStatus(200);
    }

    public function testProfile_validation(): void
    {
        /** @todo This test is incomplete. */
        $this->get('/profile_validation')
            ->assertStatus(200);
    }
}
