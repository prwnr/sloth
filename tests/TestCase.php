<?php

namespace Tests;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, WithFaker, RefreshDatabase;

    protected $team;

    /** @var MockInterface */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('sloth:install');

        $this->team = factory(Team::class)->create();

        $this->user = \Mockery::mock(User::class);
        $this->user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->user->shouldReceive('getAttribute')->with('team')->andReturn($this->team);
        $this->user->shouldReceive('getAttribute')->with('team_id')->andReturn($this->team->id);
    }
}
