<?php

namespace App\Services;

use App\Models\MasterUser;
use App\Models\MasterMoodle;
use Illuminate\Support\Facades\Log;
use App\Libraries\MoodleRest;

class MoodleService
{
    /**
     * Create Moodle account for user
     */
    public function createMoodleAccount(MasterUser $masterUser)
    {
        try {
            $moodle = new MoodleRest(
                config('services.moodle.url'),
                config('services.moodle.token')
            );

            // Prepare user data for Moodle
            $userData = [
                'users' => [[
                    'username' => $masterUser->va_number,
                    'password' => $masterUser->plain_password ?? $this->generateDefaultPassword(),
                    'lastname' => $masterUser->user->name ?? 'Peserta',
                    'firstname' => $masterUser->va_number,
                    'email' => 'pmb' . $masterUser->va_number . '@pmb.ubpkarawang.ac.id',
                    'customfields' => [
                        ['type' => 'nip', 'value' => $masterUser->va_number],
                        ['type' => 'klas', 'value' => 'usm2025'],
                        ['type' => 'nomortelpon', 'value' => $masterUser->user->phone ?? '089797979899'],
                    ],
                ]]
            ];

            Log::info('🚀 Creating Moodle account', [
                'va_number' => $masterUser->va_number,
                'user_id' => $masterUser->user_id
            ]);

            $response = $moodle->request('core_user_create_users', $userData);

            if (isset($response[0]['id'])) {
                $moodleUserId = $response[0]['id'];
                
                // Enroll to course
                $enrolSuccess = $this->enrolToCourse($moodleUserId);
                
                // Save Moodle user data
                $moodleUser = MasterMoodle::updateOrCreate(
                    ['master_user_id' => $masterUser->id],
                    [
                        'moodle_user_id' => $moodleUserId,
                        'username' => $masterUser->va_number,
                        'status' => 'success',
                        'enrol_status' => $enrolSuccess ? 'enrolled' : 'enrol_failed',
                        'error_message' => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                Log::info('✅ Moodle account created successfully', [
                    'va_number' => $masterUser->va_number,
                    'moodle_user_id' => $moodleUserId,
                    'enrol_status' => $enrolSuccess ? 'enrolled' : 'enrol_failed'
                ]);
                
                return [
                    'success' => true,
                    'moodle_user_id' => $moodleUserId,
                    'message' => 'Akun Moodle berhasil dibuat'
                ];

            } else {
                $errorMessage = $response['message'] ?? 'Unknown Moodle API error';
                
                MasterMoodle::updateOrCreate(
                    ['master_user_id' => $masterUser->id],
                    [
                        'username' => $masterUser->va_number,
                        'status' => 'failed',
                        'enrol_status' => 'not_enrolled',
                        'error_message' => $errorMessage,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                Log::error('❌ Failed to create Moodle account', [
                    'va_number' => $masterUser->va_number,
                    'error' => $errorMessage
                ]);
                
                return [
                    'success' => false,
                    'message' => $errorMessage
                ];
            }

        } catch (\Throwable $th) {
            $errorMessage = $th->getMessage();
            
            MasterMoodle::updateOrCreate(
                ['master_user_id' => $masterUser->id],
                [
                    'username' => $masterUser->va_number,
                    'status' => 'failed',
                    'enrol_status' => 'not_enrolled',
                    'error_message' => $errorMessage,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            
            Log::error('💥 Exception creating Moodle account', [
                'va_number' => $masterUser->va_number,
                'error' => $errorMessage,
                'trace' => $th->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => $errorMessage
            ];
        }
    }

    /**
     * Enrol user to course
     */
    private function enrolToCourse($moodleUserId)
    {
        try {
            $moodle = new MoodleRest(
                config('services.moodle.url'),
                config('services.moodle.token')
            );

            $enrolments = [
                'enrolments' => [[
                    'roleid' => 5, // Student role
                    'userid' => $moodleUserId,
                    'courseid' => config('services.moodle.default_course_id', 384),
                ]]
            ];

            $response = $moodle->request('enrol_manual_enrol_users', $enrolments);
            
            Log::info('✅ Successfully enrolled user to course', [
                'moodle_user_id' => $moodleUserId,
                'course_id' => config('services.moodle.default_course_id', 384)
            ]);
            
            return true;

        } catch (\Throwable $th) {
            Log::error('❌ Failed to enrol user', [
                'moodle_user_id' => $moodleUserId,
                'error' => $th->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Generate default password
     */
    private function generateDefaultPassword()
    {
        return 'Password' . date('Y');
    }

    /**
     * Check if user has Moodle account
     */
    public function hasMoodleAccount($masterUserId)
    {
        return MasterMoodle::where('master_user_id', $masterUserId)
            ->where('status', 'success')
            ->exists();
    }

    /**
     * Get Moodle account status
     */
    public function getMoodleAccountStatus($masterUserId)
    {
        return MasterMoodle::where('master_user_id', $masterUserId)->first();
    }
}