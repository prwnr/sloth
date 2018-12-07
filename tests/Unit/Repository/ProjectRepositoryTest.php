<?php

namespace Tests\Unit\Repository;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Repositories\ProjectRepository;
use ErrorException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class ProjectRepositoryTest extends TestCase
{
    /**
     * @var MockInterface
     */
    private $project;

    public function setUp(): void
    {
        $this->project = \Mockery::mock(Project::class);
        parent::setUp();
    }

    public function testFindsModel(): void
    {
        $expected = factory(Project::class)->create();
        $repository = new ProjectRepository(new Project());

        $actual = $repository->find($expected->id);
        $this->assertInstanceOf(Project::class, $actual);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
    }

    public function testFindsModelWithRelation(): void
    {
        $expected = factory(Project::class)->create();
        $repository = new ProjectRepository(new Project());
        $actual = $repository->findWith($expected->id, ['billing']);

        $this->assertInstanceOf(Project::class, $actual);
        $this->assertEquals($expected->attributesToArray(), $actual->attributesToArray());
        $this->assertTrue($actual->relationLoaded('billing'));
    }

    public function testThrowsModelNotFoundExceptionOnFind(): void
    {
        $repository = new ProjectRepository(new Project());

        $this->expectException(ModelNotFoundException::class);
        $repository->find(0);
    }

    public function testThrowsModelNotFoundExceptionOnFindWithRelation(): void
    {
        $repository = new ProjectRepository(new Project());

        $this->expectException(ModelNotFoundException::class);
        $repository->findWith(0, ['billing']);
    }

    public function testReturnCollection(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = new Collection([
            new Project(),
            new Project(),
            new Project()
        ]);

        $this->project->shouldReceive('where->get')
            ->with('team_id', $this->user->team_id)
            ->with(['*'])
            ->andReturn($expected);

        $repository = new ProjectRepository($this->project);
        $actual = $repository->all();

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
    }

    public function testReturnCollectionWithRelations(): void
    {
        $this->actingAs($this->user, 'api');

        $expected = new Collection([
            (new Project())->setRelation('client', factory(Client::class)->create()),
            (new Project())->setRelation('client', factory(Client::class)->create()),
            (new Project())->setRelation('client', factory(Client::class)->create())
        ]);

        $this->project->shouldReceive('where->with->get')
            ->with('team_id', $this->user->team_id)
            ->with(['client'])
            ->with(['*'])
            ->andReturn($expected);

        $repository = new ProjectRepository($this->project);
        $actual = $repository->allWith(['client']);

        $this->assertEquals($expected->take(1), $actual->take(1));
        $this->assertEquals(3, $actual->count());
        $this->assertTrue($actual->first()->relationLoaded('client'));
    }

    public function testCreatesModel(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team')->andReturn(factory(Team::class)->create());
        $this->actingAs($user, 'api');

        $expectedProject = $this->makeProjectData();
        $expectedTasks[] = $this->makeTaskData();
        $expectedBilling = $this->makeBillingData();
        $data = $expectedProject;
        $data['client'] = factory(Client::class)->create(['team_id' => $user->team->id])->id;
        $data['tasks'] = $expectedTasks;
        $data['billing_rate'] = $expectedBilling['rate'];
        $data['billing_type'] = $expectedBilling['type'];
        $data['billing_currency'] = $expectedBilling['currency_id'];

        $repository = new ProjectRepository(new Project());
        $actual = $repository->create($data);

        $this->assertInstanceOf(Project::class, $actual);
        $this->assertEquals($expectedProject['name'], $actual->name);
        $this->assertEquals($expectedProject['code'], $actual->code);
        $this->assertArraySubset(['billing', 'client'], $actual->getQueueableRelations());
        $this->assertCount(1, $actual->tasks);
        $this->assertEquals($expectedTasks[0]['type'], $actual->tasks->first()->type);
    }

    public function testCreatesModelWithoutTasks(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team')->andReturn(factory(Team::class)->create());
        $this->actingAs($user, 'api');

        $expectedProject = $this->makeProjectData($user);
        $expectedBilling = $this->makeBillingData();
        $data = $expectedProject;
        $data['tasks'] = [];
        $data['client'] = factory(Client::class)->create(['team_id' => $user->team->id])->id;
        $data['billing_rate'] = $expectedBilling['rate'];
        $data['billing_type'] = $expectedBilling['type'];
        $data['billing_currency'] = $expectedBilling['currency_id'];

        $repository = new ProjectRepository(new Project());
        $actual = $repository->create($data);

        $this->assertInstanceOf(Project::class, $actual);
        $this->assertEquals($expectedProject['name'], $actual->name);
        $this->assertEquals($expectedProject['code'], $actual->code);
        $this->assertArraySubset(['billing', 'client'], $actual->getQueueableRelations());
        $this->assertCount(0, $actual->tasks);
    }

    public function testFailsToCreateModel(): void
    {
        $user = \Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('team')->andReturn(factory(Team::class)->create());
        $this->actingAs($user, 'api');

        $repository = new ProjectRepository(new Project());

        $this->expectException(ErrorException::class);
        $repository->create([]);
    }

    public function testUpdatesModel(): void
    {
        $model = factory(Project::class)->create();
        $repository = new ProjectRepository(new Project());

        $expectedProject = $this->makeProjectData();
        $task = $this->makeTaskData();
        $task['billable'] = true;
        $expectedTasks[] = $task;
        $expectedBilling = $this->makeBillingData();
        $data = $expectedProject;
        $data['client'] = factory(Client::class)->create()->id;
        $data['tasks'] = $expectedTasks;
        $data['billing_rate'] = $expectedBilling['rate'];
        $data['billing_type'] = $expectedBilling['type'];
        $data['billing_currency'] = $expectedBilling['currency_id'];

        $actual = $repository->update($model->id, $data);

        $this->assertInstanceOf(Project::class, $actual);
        $this->assertEquals($expectedProject['name'], $actual->name);
        $this->assertEquals($expectedProject['code'], $actual->code);
        $this->assertArraySubset($expectedBilling, $actual->billing->attributesToArray());
        $this->assertEquals($data['client'], $actual->client->id);
        $this->assertEquals($expectedTasks[0]['type'], $actual->tasks->first()->type);
        $this->assertEquals($expectedTasks[0]['name'], $actual->tasks->first()->name);
        $this->assertEquals($expectedTasks[0]['currency'], $actual->tasks->first()->currency->id);
    }

    public function testUpdatesModelWithNonBillableTask(): void
    {
        $model = factory(Project::class)->create();
        $repository = new ProjectRepository(new Project());

        $expectedProject = $this->makeProjectData();
        $task = $this->makeTaskData();
        $task['billable'] = false;
        $expectedTasks[] = $task;
        $expectedBilling = $this->makeBillingData();
        $data = $expectedProject;
        $data['client'] = factory(Client::class)->create()->id;
        $data['tasks'] = $expectedTasks;
        $data['billing_rate'] = $expectedBilling['rate'];
        $data['billing_type'] = $expectedBilling['type'];
        $data['billing_currency'] = $expectedBilling['currency_id'];

        $actual = $repository->update($model->id, $data);

        $this->assertInstanceOf(Project::class, $actual);
        $this->assertEquals($expectedProject['name'], $actual->name);
        $this->assertEquals($expectedProject['code'], $actual->code);
        $this->assertArraySubset($expectedBilling, $actual->billing->attributesToArray());
        $this->assertEquals($data['client'], $actual->client->id);
        $this->assertEquals($expectedTasks[0]['type'], $actual->tasks->first()->type);
        $this->assertEquals($expectedTasks[0]['name'], $actual->tasks->first()->name);
        $this->assertNull($actual->tasks->first()->currency);
    }

    public function testThrowsModelNotFoundExceptionOnModelUpdateWithNotExistingModel(): void
    {
        $this->project->shouldReceive('findOrFail')->with(1, ['*'])->andThrowExceptions([new ModelNotFoundException()]);

        $repository = new ProjectRepository($this->project);

        $this->expectException(ModelNotFoundException::class);
        $repository->update(1, []);
    }

    public function testDeletesModel(): void
    {
        $model = factory(Project::class)->create();
        $member = factory(Team\Member::class)->create();
        $member->projects()->attach($model);
        $repository = new ProjectRepository(new Project());

        $this->assertTrue($repository->delete($model->id));
        $this->assertCount(0, Team\Member::query()->whereHas('projects', function ($query) use ($model) {
            $query->where('project_id', $model->id);
        })->get());
    }

    public function testDoesNotDeleteModel(): void
    {
        $model = new Project($this->makeClientData());
        $this->project->shouldReceive('findOrFail')->with(1, ['*'])->andReturn($model);

        $repository = new ProjectRepository($this->project);

        $this->assertFalse($repository->delete(1));
    }

    public function testFailsToDeleteModel(): void
    {
        $repository = new ProjectRepository(new project());

        $this->expectException(ModelNotFoundException::class);
        $repository->delete(0);
    }

    private function makeClientData(): array
    {
        return [
            'company_name' => $this->faker->company,
            'street' => $this->faker->streetAddress,
            'zip' => $this->faker->postcode,
            'country' => $this->faker->country,
            'city' => $this->faker->city,
            'vat' => $this->faker->numberBetween(11111,99999),
            'fullname' => $this->faker->name,
            'email' => $this->faker->email
        ];
    }

    private function makeProjectData(): array
    {
        return [
            'code' => $this->faker->toUpper($this->faker->randomLetter . $this->faker->randomLetter . $this->faker->randomLetter),
            'name' => $this->faker->company,
            'budget' => $this->faker->numberBetween(0, 999999),
            'budget_period' => $this->faker->randomKey(Project::BUDGET_PERIOD),
            'budget_currency' => Currency::all()->random()->id,
        ];
    }

    /**
     * @return array
     */
    private function makeTaskData(): array
    {
        return [
            'type' => $this->faker->word,
            'name' => $this->faker->word,
            'billable' => $this->faker->boolean,
            'currency' => Currency::all()->random()->id,
            'is_deleted' => $this->faker->boolean
        ];
    }

    private function makeBillingData(): array
    {
        return [
            'rate' => $this->faker->numberBetween(1, 500),
            'currency_id' => Currency::all()->random()->id,
            'type' => $this->faker->randomElement(Billing::getRateTypes())
        ];
    }
}
