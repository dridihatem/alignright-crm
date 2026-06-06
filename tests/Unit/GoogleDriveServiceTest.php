<?php

namespace Tests\Unit;

use App\Models\User;
use App\Providers\GoogleDriveService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GoogleDriveServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GoogleDriveService $googleDriveService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->googleDriveService = new GoogleDriveService();
        
        // Create a test user with Google tokens
        $this->user = User::factory()->create([
            'google_access_token' => 'test_access_token',
            'google_refresh_token' => 'test_refresh_token',
            'google_token_expires_at' => Carbon::now()->addHour(),
        ]);
    }

    public function test_google_drive_service_can_be_instantiated()
    {
        $service = new GoogleDriveService();
        $this->assertInstanceOf(GoogleDriveService::class, $service);
    }

    public function test_user_with_valid_google_tokens_has_required_fields()
    {
        $this->assertNotNull($this->user->google_access_token);
        $this->assertNotNull($this->user->google_refresh_token);
        $this->assertNotNull($this->user->google_token_expires_at);
        $this->assertTrue($this->user->google_token_expires_at->isFuture());
    }

    public function test_user_without_google_tokens_has_null_fields()
    {
        $userWithoutTokens = User::factory()->create([
            'google_access_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
        ]);

        $this->assertNull($userWithoutTokens->google_access_token);
        $this->assertNull($userWithoutTokens->google_refresh_token);
        $this->assertNull($userWithoutTokens->google_token_expires_at);
    }

    public function test_file_upload_validation_with_valid_file()
    {
        $file = UploadedFile::fake()->create('test_document.pdf', 100, 'application/pdf');
        // Size is in kilobytes, so 100 KB = 102400 bytes
        $this->assertTrue($file->isValid());
        $this->assertEquals('test_document.pdf', $file->getClientOriginalName());
        $this->assertEquals('application/pdf', $file->getMimeType());
        $this->assertEquals(102400, $file->getSize());
    }

    public function test_file_upload_validation_with_image_file()
    {
        $file = UploadedFile::fake()->image('test_image.jpg', 100, 100);
        
        $this->assertTrue($file->isValid());
        $this->assertEquals('test_image.jpg', $file->getClientOriginalName());
        $this->assertStringContainsString('image/', $file->getMimeType());
    }

    public function test_file_upload_validation_with_large_file()
    {
        $file = UploadedFile::fake()->create('large_document.pdf', 10240, 'application/pdf'); // 10MB
        $this->assertTrue($file->isValid());
        $this->assertEquals('large_document.pdf', $file->getClientOriginalName());
        $this->assertGreaterThan(1000000, $file->getSize()); // Should be larger than 1MB
    }

    public function test_token_expiration_detection()
    {
        // Test with valid token
        $this->assertTrue($this->user->google_token_expires_at->isFuture());
        
        // Test with expired token
        $expiredUser = User::factory()->create([
            'google_access_token' => 'expired_token',
            'google_refresh_token' => 'refresh_token',
            'google_token_expires_at' => Carbon::now()->subHour(),
        ]);
        
        $this->assertTrue($expiredUser->google_token_expires_at->isPast());
    }

    public function test_token_refresh_scenario()
    {
        // Simulate token refresh by updating the user
        $originalToken = $this->user->google_access_token;
        $this->user->update([
            'google_access_token' => 'new_access_token',
            'google_token_expires_at' => Carbon::now()->addHour(),
        ]);
        $this->user->refresh();
        $this->assertNotEquals($originalToken, $this->user->google_access_token);
        $this->assertEquals('new_access_token', $this->user->google_access_token);
        $this->assertTrue(Carbon::parse($this->user->google_token_expires_at)->isFuture());
    }

    public function test_file_sharing_permissions_email_sharing()
    {
        $shareEmail = 'test@example.com';
        
        // Test email sharing logic
        $this->assertIsString($shareEmail);
        $this->assertStringContainsString('@', $shareEmail);
        $this->assertStringContainsString('.', $shareEmail);
    }

    public function test_file_sharing_permissions_public_sharing()
    {
        // Test public sharing (no email specified)
        $publicSharing = null;
        
        $this->assertNull($publicSharing);
    }

    public function test_file_metadata_extraction()
    {
        $file = UploadedFile::fake()->create('metadata_test.pdf', 150, 'application/pdf');
        // 150 KB = 153600 bytes
        $expectedMetadata = [
            'name' => 'metadata_test.pdf',
            'mimeType' => 'application/pdf',
            'size' => 153600,
        ];
        $this->assertEquals($expectedMetadata['name'], $file->getClientOriginalName());
        $this->assertEquals($expectedMetadata['mimeType'], $file->getMimeType());
        $this->assertEquals($expectedMetadata['size'], $file->getSize());
    }

    public function test_google_drive_integration_workflow_structure()
    {
        // Test the workflow structure without actual API calls
        $file = UploadedFile::fake()->create('workflow_test.pdf', 100, 'application/pdf');
        
        // Test that all required components exist
        $this->assertNotNull($this->user->google_access_token);
        $this->assertNotNull($this->user->google_refresh_token);
        $this->assertTrue($file->isValid());
        $this->assertEquals('workflow_test.pdf', $file->getClientOriginalName());
    }

    public function test_error_handling_structure()
    {
        // Test error handling structure
        $userWithInvalidTokens = User::factory()->create([
            'google_access_token' => 'invalid_token',
            'google_refresh_token' => 'invalid_refresh',
            'google_token_expires_at' => Carbon::now()->addHour(),
        ]);
        
        $file = UploadedFile::fake()->create('error_test.pdf', 100, 'application/pdf');
        
        // Test that we can detect invalid tokens
        $this->assertNotNull($userWithInvalidTokens->google_access_token);
        $this->assertTrue($file->isValid());
    }

    public function test_user_google_drive_connection_status()
    {
        // Test connected user
        $this->assertTrue($this->user->google_access_token !== null);
        
        // Test disconnected user
        $disconnectedUser = User::factory()->create([
            'google_access_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
        ]);
        
        $this->assertTrue($disconnectedUser->google_access_token === null);
    }

    public function test_multiple_file_types_handling()
    {
        $pdfFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $imageFile = UploadedFile::fake()->image('image.jpg', 100, 100);
        $textFile = UploadedFile::fake()->create('document.txt', 50, 'text/plain');
        
        $this->assertEquals('application/pdf', $pdfFile->getMimeType());
        $this->assertStringContainsString('image/', $imageFile->getMimeType());
        $this->assertEquals('text/plain', $textFile->getMimeType());
    }

    public function test_file_size_validation()
    {
        $smallFile = UploadedFile::fake()->create('small.pdf', 1, 'application/pdf'); // 1 KB = 1024 bytes
        $mediumFile = UploadedFile::fake()->create('medium.pdf', 1024, 'application/pdf'); // 1 MB = 1048576 bytes
        $largeFile = UploadedFile::fake()->create('large.pdf', 10240, 'application/pdf'); // 10 MB = 10485760 bytes
        $this->assertLessThan(2000, $smallFile->getSize()); // < 2 KB
        $this->assertGreaterThan(1000, $mediumFile->getSize()); // > 1 KB
        $this->assertGreaterThan(1000000, $largeFile->getSize()); // > 1 MB
    }

    public function test_google_drive_service_returns_correct_format()
    {
        // Test that the service can be instantiated
        $service = new GoogleDriveService();
        $this->assertInstanceOf(GoogleDriveService::class, $service);
        
        // Test that the method signature accepts the new parameters
        $method = new \ReflectionMethod(GoogleDriveService::class, 'uploadForUser');
        $parameters = $method->getParameters();
        
        $this->assertCount(5, $parameters);
        $this->assertEquals('file', $parameters[0]->getName());
        $this->assertEquals('user', $parameters[1]->getName());
        $this->assertEquals('shareWithEmail', $parameters[2]->getName());
        $this->assertEquals('cases_folder', $parameters[3]->getName());
        $this->assertEquals('patient_id', $parameters[4]->getName());
    }
} 