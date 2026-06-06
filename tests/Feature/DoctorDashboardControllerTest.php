<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CasePatient;
use App\Models\Patient;
use App\Models\Tickets;
use App\Services\CaseService;
use App\Services\PatientService;
use App\Services\TicketService;
use App\Repositories\CaseRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Exception;

class DoctorDashboardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $doctor;
    protected $caseService;
    protected $patientService;
    protected $ticketService;
    protected $caseRepository;
    protected $patientRepository;
    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a doctor user
        $this->doctor = User::factory()->create([
            'role_id' => 2, // Doctor role
            'status' => 'active'
        ]);

        // Mock services and repositories
        $this->caseService = $this->createMock(CaseService::class);
        $this->patientService = $this->createMock(PatientService::class);
        $this->ticketService = $this->createMock(TicketService::class);
        $this->caseRepository = $this->createMock(CaseRepository::class);
        $this->patientRepository = $this->createMock(PatientRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);

        // Bind mocks to container
        $this->app->instance(CaseService::class, $this->caseService);
        $this->app->instance(PatientService::class, $this->patientService);
        $this->app->instance(TicketService::class, $this->ticketService);
        $this->app->instance(CaseRepository::class, $this->caseRepository);
        $this->app->instance(PatientRepository::class, $this->patientRepository);
        $this->app->instance(UserRepository::class, $this->userRepository);
    }

    /** @test */
    public function doctor_can_access_dashboard()
    {
        $this->actingAs($this->doctor);

        // Mock service responses
        $this->caseService->expects($this->once())
            ->method('getDashboardStats')
            ->with($this->doctor->id)
            ->willReturn([
                'total_cases' => 10,
                'pending_cases' => 3,
                'completed_cases' => 7
            ]);

        $this->patientService->expects($this->once())
            ->method('getPatientStats')
            ->with($this->doctor->id)
            ->willReturn([
                'total_patients' => 15,
                'active_patients' => 12,
                'new_patients_this_month' => 5
            ]);

        $this->ticketService->expects($this->once())
            ->method('getTicketStats')
            ->with($this->doctor->id)
            ->willReturn([
                'total_tickets' => 8,
                'open_tickets' => 4,
                'closed_tickets' => 4
            ]);

        $this->caseRepository->expects($this->once())
            ->method('getByDoctor')
            ->with($this->doctor->id, ['patient'])
            ->willReturn(collect([]));

        $response = $this->get(route('doctor.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('doctor.dashboard');
    }

    /** @test */
    public function dashboard_handles_service_errors_gracefully()
    {
        $this->actingAs($this->doctor);

        $this->caseService->expects($this->once())
            ->method('getDashboardStats')
            ->willThrowException(new Exception('Service error'));

        $response = $this->get(route('doctor.dashboard'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Failed to load dashboard data');
    }

    /** @test */
    public function doctor_can_get_latest_cases_via_ajax()
    {
        $this->actingAs($this->doctor);

        // Create some test cases
        $cases = CasePatient::factory()->count(3)->create([
            'doctor_id' => $this->doctor->id
        ]);

        $this->caseRepository->expects($this->once())
            ->method('getWithFilters')
            ->willReturn($cases->toQuery());

        $response = $this->getJson(route('doctor.latest_cases'));

        $response->assertStatus(200);
    }

    /** @test */
    public function doctor_can_check_doctor_code()
    {
        $this->actingAs($this->doctor);

        $testDoctor = User::factory()->create([
            'role_id' => 2,
            'code_parrent' => 'TEST123',
            'status' => 'active'
        ]);

        $this->userRepository->expects($this->once())
            ->method('checkDoctorCode')
            ->with('TEST123')
            ->willReturn($testDoctor);

        $response = $this->getJson(route('check.code.doctor', ['code_parrent' => 'TEST123']));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => $testDoctor->toArray()
        ]);
    }

    /** @test */
    public function doctor_code_check_returns_error_for_invalid_code()
    {
        $this->actingAs($this->doctor);

        $this->userRepository->expects($this->once())
            ->method('checkDoctorCode')
            ->with('INVALID')
            ->willReturn(null);

        $response = $this->getJson(route('check.code.doctor', ['code_parrent' => 'INVALID']));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'error',
            'data' => ''
        ]);
    }

    /** @test */
    public function doctor_code_check_handles_exceptions()
    {
        $this->actingAs($this->doctor);

        $this->userRepository->expects($this->once())
            ->method('checkDoctorCode')
            ->willThrowException(new Exception('Database error'));

        $response = $this->getJson(route('check.code.doctor', ['code_parrent' => 'TEST123']));

        $response->assertStatus(500);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Failed to check doctor code'
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get(route('doctor.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function non_doctor_user_cannot_access_dashboard()
    {
        $technician = User::factory()->create(['role_id' => 3]); // Technician role
        $this->actingAs($technician);

        $response = $this->get(route('doctor.dashboard'));

        // This would typically be handled by middleware, but we're testing the controller logic
        $response->assertStatus(403);
    }

    /** @test */
    public function dashboard_includes_monthly_case_totals()
    {
        $this->actingAs($this->doctor);

        // Mock the case repository to return cases with specific creation dates
        $cases = collect([
            CasePatient::factory()->make(['created_at' => now()->subDays(5)]),
            CasePatient::factory()->make(['created_at' => now()->subDays(10)]),
            CasePatient::factory()->make(['created_at' => now()->subDays(15)]),
        ]);

        $this->caseRepository->expects($this->exactly(2))
            ->method('getByDoctor')
            ->willReturn($cases);

        // Mock other services
        $this->caseService->expects($this->once())
            ->method('getDashboardStats')
            ->willReturn([]);

        $this->patientService->expects($this->once())
            ->method('getPatientStats')
            ->willReturn([]);

        $this->ticketService->expects($this->once())
            ->method('getTicketStats')
            ->willReturn([]);

        $response = $this->get(route('doctor.dashboard'));

        $response->assertStatus(200);
    }

    /** @test */
    public function latest_cases_handles_filters_correctly()
    {
        $this->actingAs($this->doctor);

        $filters = [
            'search' => 'test case',
            'status' => 'pending'
        ];

        $this->caseRepository->expects($this->once())
            ->method('getWithFilters')
            ->with($this->arrayHasKey('doctor_id'))
            ->willReturn(collect([])->toQuery());

        $response = $this->getJson(route('doctor.latest_cases', $filters));

        $response->assertStatus(200);
    }

    /** @test */
    public function latest_cases_handles_exceptions()
    {
        $this->actingAs($this->doctor);

        $this->caseRepository->expects($this->once())
            ->method('getWithFilters')
            ->willThrowException(new Exception('Database error'));

        $response = $this->getJson(route('doctor.latest_cases'));

        $response->assertStatus(500);
        $response->assertJson(['error' => 'Failed to retrieve cases']);
    }
}

