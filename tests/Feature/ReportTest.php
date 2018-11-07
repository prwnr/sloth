<?php

namespace Tests\Feature;

use App\Models\Date\DateRange;
use App\Models\Project;
use App\Models\Role;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportTest extends FeatureTestCase
{

    private $reportItemsStructure = [
        'items' => [
            [
                'user_name', 'client', 'project', 'task', 'date', 'duration', 'billable', 'in_progress',
                'details' => [
                    'description',
                    'project_salary' => [
                        'currency', 'amount'
                    ],
                    'member_salary' => [
                        'currency', 'amount'
                    ],
                    'client_salary' => [
                        'currency', 'amount'
                    ]
                ],
            ]
        ]
    ];

    private $createdProjects = [];

    public function testFullReportIsShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $this->makeLogs();

        $filters = [
            'filters' => []
        ];
        $response = $this->json(Request::METHOD_POST, '/api/reports', $filters);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure($this->reportItemsStructure);
        $response->assertJsonCount(10, 'items');
    }

    public function testFullReportIsFilteredCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $this->makeLogs();

        $filters = [
            'filters' => [
                'projects' => [$this->faker->randomElement($this->createdProjects)]
            ]
        ];
        $response = $this->json(Request::METHOD_POST, '/api/reports', $filters);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure($this->reportItemsStructure);
        $response->assertJsonCount(5, 'items');
    }

    public function testMemberReportIsShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->makeLogs();

        $filters = [
            'filters' => []
        ];
        $response = $this->json(Request::METHOD_POST, "/api/reports/{$this->user->member()->id}", $filters);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure($this->reportItemsStructure);

        $response->assertJsonCount(10, 'items');
    }

    public function testMemberReportIsFilteredCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->makeLogs();

        $filters = [
            'filters' => [
                'projects' => [$this->faker->randomElement($this->createdProjects)]
            ]
        ];
        $response = $this->json(Request::METHOD_POST, "/api/reports/{$this->user->member()->id}", $filters);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure($this->reportItemsStructure);

        $response->assertJsonCount(5, 'items');
    }

    public function testUserHoursMonthlyReportIsShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');

        $this->makeLogs();

        $period = DateRange::MONTH;
        $response = $this->json(Request::METHOD_GET, "/api/reports/{$this->user->member()->id}/hours/$period");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'hours' => [],
            'labels' => []
        ]);
    }

    public function testUserHoursWeeklyReportIsShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');

        $this->makeLogs();

        $period = DateRange::WEEK;
        $response = $this->json(Request::METHOD_GET, "/api/reports/{$this->user->member()->id}/hours/$period");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'hours' => [],
            'labels' => []
        ]);
    }

    public function testUserProjectsReportIsShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');

        $this->makeLogs();

        $period = DateRange::MONTH;
        $response = $this->json(Request::METHOD_GET, "/api/reports/{$this->user->member()->id}/projects/$period");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'hours' => [],
            'labels' => []
        ]);
    }

    public function testSalesMonthlyReportIsShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $this->makeLogs();

        $period = DateRange::MONTH;
        $response = $this->json(Request::METHOD_GET, "/api/reports/sales/$period");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'sales' => [
                '*' => [
                    'label', 'data'
                ],
                '*' => [
                    'label', 'data'
                ]
            ]
        ]);
    }

    public function testSalesWeeklyReportIsShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $this->makeLogs();

        $period = DateRange::WEEK;
        $response = $this->json(Request::METHOD_GET, "/api/reports/sales/$period");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'sales' => [
                '*' => [
                    'label', 'data'
                ],
                '*' => [
                    'label', 'data'
                ]
            ]
        ]);
    }

    private function makeLogs(): void
    {
        for ($i = 0; $i < 2; $i++) {
            $project = factory(Project::class)->create(['team_id' => $this->user->team_id]);
            $this->createdProjects[] = $project->id;
            for ($j = 0; $j < 5; $j++) {
                factory(TimeLog::class)->create([
                    'member_id' => $this->user->member()->id,
                    'project_id' => $project->id,
                    'start' => null,
                    'created_at' => $this->faker->dateTimeThisMonth()
                ]);
            }
        }
    }
}
