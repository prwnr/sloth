<?php

namespace Tests;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, WithFaker;

    /** @var MockInterface */
    protected $team;

    /** @var MockInterface */
    protected $user;

    protected function mockTeamAndUser(): void
    {
        $this->team = \Mockery::mock(Team::class);
        $this->team->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->user = \Mockery::mock(User::class);
        $this->user->shouldReceive('getAttribute')->with('team')->andReturn($this->team);
        $this->user->shouldReceive('getAttribute')->with('team_id')->andReturn($this->team->id);
    }
}
