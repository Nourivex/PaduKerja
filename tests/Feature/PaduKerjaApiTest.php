<?php

namespace Tests\Feature;

use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaduKerjaApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $recruiter;
    protected User $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recruiter = User::create([
            'name' => 'Recruiter Test',
            'email' => 'recruiter@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'recruiter',
        ]);

        $this->applicant = User::create([
            'name' => 'Applicant Test',
            'email' => 'applicant@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'applicant',
            'skills' => ['PHP', 'Laravel', 'MySQL'],
        ]);
    }

    public function test_paket_d_login_and_token_generation(): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['token', 'token_type', 'user'],
                'meta' => ['timestamp', 'version'],
            ]);

        return $response->json('data.token');
    }

    public function test_paket_d_update_skills(): void
    {
        $token = $this->test_paket_d_login_and_token_generation();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson('/api/profile/skills', [
            'skills' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.skills_count', 4);
    }

    public function test_paket_a_jobs_list_and_filter(): void
    {
        JobVacancy::create([
            'title' => 'Backend Engineer',
            'company' => 'PT Maju Jaya',
            'description' => 'Test Desc',
            'location' => 'Jakarta',
            'quota' => 2,
            'requirements' => [
                ['skill' => 'PHP', 'weight' => 3, 'level' => 'required'],
                ['skill' => 'Laravel', 'weight' => 3, 'level' => 'required'],
            ],
            'status' => 'open',
            'posted_by' => $this->recruiter->id,
        ]);

        // Test GET all
        $response = $this->getJson('/api/jobs');
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data');

        // Test filter matching
        $filterMatch = $this->getJson('/api/jobs?location=Jakarta');
        $filterMatch->assertStatus(200)->assertJsonCount(1, 'data');

        // Test filter non-matching
        $filterNone = $this->getJson('/api/jobs?location=Surabaya');
        $filterNone->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_paket_a_recruiter_post_job(): void
    {
        $loginRes = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ]);
        $token = $loginRes->json('data.token');

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/jobs', [
            'title' => 'DevOps Engineer',
            'company' => 'PT Cloud Tech',
            'description' => 'Cloud management',
            'location' => 'Bandung',
            'quota' => 1,
            'requirements' => [
                ['skill' => 'Docker', 'level' => 'required', 'weight' => 3],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', 'DevOps Engineer');
    }

    public function test_paket_b_apply_and_scoring(): void
    {
        $job = JobVacancy::create([
            'title' => 'PHP Developer',
            'company' => 'PT Maju Jaya',
            'description' => 'Test Desc',
            'location' => 'Jakarta',
            'quota' => 1,
            'requirements' => [
                ['skill' => 'PHP', 'weight' => 3, 'level' => 'required'],
                ['skill' => 'Laravel', 'weight' => 3, 'level' => 'required'],
                ['skill' => 'Docker', 'weight' => 2, 'level' => 'important'],
            ],
            'status' => 'open',
            'posted_by' => $this->recruiter->id,
        ]);

        $loginRes = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ]);
        $token = $loginRes->json('data.token');

        // Applicant has PHP and Laravel (weight 3+3 = 6). Total weight = 3+3+2 = 8.
        // Score: 6/8 = 75%
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/applications', [
            'job_id' => $job->id,
            'cover_letter' => 'Saya tertarik.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.matchmaking_result.match_score', 75)
            ->assertJsonPath('data.matchmaking_result.match_category', 'good');
    }

    public function test_paket_c_pipeline_update_and_view(): void
    {
        $job = JobVacancy::create([
            'title' => 'PHP Developer',
            'company' => 'PT Maju Jaya',
            'description' => 'Test Desc',
            'location' => 'Jakarta',
            'requirements' => [
                ['skill' => 'PHP', 'weight' => 3, 'level' => 'required'],
            ],
            'status' => 'open',
            'posted_by' => $this->recruiter->id,
        ]);

        $applicantToken = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $recruiterToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        // Apply
        $appRes = $this->withHeaders(['Authorization' => "Bearer {$applicantToken}"])
            ->postJson('/api/applications', ['job_id' => $job->id]);
        $appId = $appRes->json('data.application_id');

        // Recruiter advances stage
        $patchRes = $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->patchJson("/api/pipelines/{$appId}/status", [
                'stage' => 'interview',
                'status' => 'in_progress',
                'notes' => 'Lolos ke interview',
            ]);

        $patchRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.current_stage', 'interview');

        // Applicant checks timeline
        $timelineRes = $this->withHeaders(['Authorization' => "Bearer {$applicantToken}"])
            ->getJson("/api/pipelines/{$appId}");

        $timelineRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.current_stage', 'interview');
    }
}
