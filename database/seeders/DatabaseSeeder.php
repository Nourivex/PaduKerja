<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\JobVacancy;
use App\Models\PipelineTimeline;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Recruiter
        $recruiter = User::firstOrCreate(
            ['email' => 'recruiter@padukerja.id'],
            [
                'name' => 'Fahren (Recruiter PT Maju)',
                'password' => Hash::make('password123'),
                'role' => 'recruiter',
                'phone' => '081234567891',
                'bio' => 'Lead Technical Recruiter',
                'location' => 'Jakarta Selatan',
            ]
        );

        // 2. Seed Applicant 1 (Budi)
        $budi = User::firstOrCreate(
            ['email' => 'budi@padukerja.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'applicant',
                'phone' => '081234567890',
                'bio' => 'Backend Developer dengan pengalaman 3 tahun di ekosistem PHP & Laravel.',
                'location' => 'Bandung',
                'skills' => ['PHP', 'Laravel', 'MySQL', 'Git'],
            ]
        );

        // 3. Seed Applicant 2 (Siti)
        $siti = User::firstOrCreate(
            ['email' => 'siti@padukerja.id'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => Hash::make('password123'),
                'role' => 'applicant',
                'phone' => '081298765432',
                'bio' => 'Software Engineer spesialis Python & Data Engineering.',
                'location' => 'Surabaya',
                'skills' => ['Python', 'Django', 'PostgreSQL', 'Docker'],
            ]
        );

        // 4. Seed Job Vacancies
        $job1 = JobVacancy::firstOrCreate(
            ['title' => 'Senior Backend Engineer', 'company' => 'PT Teknologi Maju'],
            [
                'description' => 'Membangun dan mengembangkan microservices REST API dengan performa tinggi untuk platform PaduKerja.',
                'location' => 'Jakarta Selatan',
                'employment_type' => 'full-time',
                'salary_min' => 12000000,
                'salary_max' => 18000000,
                'quota' => 3,
                'filled' => 0,
                'requirements' => [
                    ['skill' => 'PHP', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'Laravel', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'MySQL', 'level' => 'important', 'weight' => 2],
                    ['skill' => 'Redis', 'level' => 'nice_to_have', 'weight' => 1],
                    ['skill' => 'Docker', 'level' => 'nice_to_have', 'weight' => 1],
                ],
                'status' => 'open',
                'posted_by' => $recruiter->id,
                'expires_at' => now()->addDays(30),
            ]
        );

        $job2 = JobVacancy::firstOrCreate(
            ['title' => 'Frontend React Specialist', 'company' => 'PT Digital Solusindo'],
            [
                'description' => 'Mengembangkan antarmuka pengguna web modern yang responsif dan terintegrasi dengan REST API.',
                'location' => 'Bandung',
                'employment_type' => 'full-time',
                'salary_min' => 9000000,
                'salary_max' => 14000000,
                'quota' => 2,
                'filled' => 0,
                'requirements' => [
                    ['skill' => 'React', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'JavaScript', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'Tailwind CSS', 'level' => 'important', 'weight' => 2],
                    ['skill' => 'TypeScript', 'level' => 'nice_to_have', 'weight' => 1],
                ],
                'status' => 'open',
                'posted_by' => $recruiter->id,
                'expires_at' => now()->addDays(45),
            ]
        );

        // 5. Seed Sample Application & Pipeline for Budi
        $application = Application::firstOrCreate(
            ['user_id' => $budi->id, 'job_vacancy_id' => $job1->id],
            [
                'cover_letter' => 'Saya tertarik mengisi posisi Senior Backend Engineer karena pengalaman saya 3 tahun menggunakan Laravel dan MariaDB.',
                'match_score' => 80.00,
                'match_category' => 'excellent',
                'matched_skills' => ['PHP', 'Laravel', 'MySQL'],
                'missing_skills' => ['Redis', 'Docker'],
                'status' => 'interview',
            ]
        );

        PipelineTimeline::firstOrCreate(
            ['application_id' => $application->id],
            [
                'current_stage' => 'interview',
                'stages' => [
                    [
                        'stage' => 'screening',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(5)->toIso8601String(),
                        'completed_at' => now()->subDays(4)->toIso8601String(),
                        'notes' => 'Automated Matchmaking Score: 80%. Lolos otomatis ke tahap berikutnya.',
                    ],
                    [
                        'stage' => 'assessment',
                        'status' => 'passed',
                        'entered_at' => now()->subDays(4)->toIso8601String(),
                        'completed_at' => now()->subDays(2)->toIso8601String(),
                        'notes' => 'Tes teknis REST API backend diselesaikan dengan nilai 92/100.',
                    ],
                    [
                        'stage' => 'interview',
                        'status' => 'in_progress',
                        'entered_at' => now()->subDays(2)->toIso8601String(),
                        'completed_at' => null,
                        'notes' => 'User interview dijadwalkan secara daring.',
                        'interview_schedule' => [
                            'datetime' => now()->addDays(2)->setTime(10, 0)->toIso8601String(),
                            'type' => 'online',
                            'meeting_url' => 'https://meet.google.com/padukerja-interview-budi',
                            'interviewer' => 'Tech Lead & HR Manager',
                        ],
                    ],
                ],
            ]
        );
    }
}
