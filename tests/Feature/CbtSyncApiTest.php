<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CbtSyncApiTest extends TestCase
{
    protected string $validToken = 'test_token_123';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.cbt.sync_token', $this->validToken);
    }

    public function test_classes_endpoint_rejects_request_without_token(): void
    {
        $response = $this->getJson('/api/cbt/classes');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized',
            ]);
    }

    public function test_students_endpoint_rejects_request_with_invalid_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->getJson('/api/cbt/students');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized',
            ]);
    }

    public function test_classes_endpoint_returns_success_with_valid_token_and_updated_at(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/classes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'class_name', 'updated_at'],
                ],
            ])
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_classes_endpoint_filters_by_since(): void
    {
        // Far future date should return empty data
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/classes?since=2099-01-01');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [],
            ]);

        // Past date should return classes
        $pastResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/classes?since=2020-01-01');

        $pastResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_students_endpoint_returns_success_with_valid_token_and_updated_at(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/students');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);

        if (count($data) > 0) {
            $student = $data[0];
            $this->assertArrayHasKey('nis', $student);
            $this->assertArrayHasKey('nisn', $student);
            $this->assertArrayHasKey('nama', $student);
            $this->assertArrayHasKey('gender', $student);
            $this->assertArrayHasKey('class_id', $student);
            $this->assertArrayHasKey('class_name', $student);
            $this->assertArrayHasKey('updated_at', $student);
        }
    }

    public function test_students_endpoint_filters_by_class_id(): void
    {
        $firstClass = DB::table('class')->first();

        if ($firstClass) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->validToken,
            ])->getJson('/api/cbt/students?class_id=' . $firstClass->id);

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                ]);

            $data = $response->json('data');
            foreach ($data as $student) {
                $this->assertEquals($firstClass->id, $student['class_id']);
            }
        }
    }

    public function test_students_endpoint_filters_by_since(): void
    {
        // Far future date should return empty data
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/students?since=2099-01-01');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [],
            ]);
    }

    public function test_fst_endpoint_returns_success_with_valid_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/fst');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'fase', 'semester', 'tahun_ajaran', 'ta', 'updated_at'],
                ],
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        // Test alias /api/cbt/academic-years
        $aliasResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/academic-years');

        $aliasResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_mapel_endpoint_returns_success_with_valid_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/mapel');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'nama_mapel', 'updated_at'],
                ],
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        // Test alias /api/cbt/subjects
        $aliasResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/subjects');

        $aliasResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }


    public function test_cbt_api_rate_limiting_enforces_30_requests_per_minute(): void
    {
        // Execute 30 valid requests within the limit
        for ($i = 0; $i < 30; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->validToken,
            ])->getJson('/api/cbt/classes');

            $response->assertStatus(200);
        }

        // The 31st request must be throttled with HTTP 429
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->validToken,
        ])->getJson('/api/cbt/classes');

        $response->assertStatus(429)
            ->assertJson([
                'status' => 'error',
                'message' => 'Too Many Requests. Rate limit exceeded (max 30 requests per minute).',
            ]);
    }
}
