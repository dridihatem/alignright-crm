# Testing GoogleDriveService

This document explains how to test the `GoogleDriveService` class and what each test covers.

## Overview

The `GoogleDriveService` class handles file uploads to Google Drive with user authentication. It manages:
- Google API authentication
- File uploads
- File sharing permissions
- Token refresh handling

## Test Structure

### Unit Tests (`tests/Unit/GoogleDriveServiceTest.php`)

Unit tests focus on testing individual components and logic without external dependencies:

- **Service Instantiation**: Tests that the service can be created
- **User Token Validation**: Tests user Google token fields
- **File Upload Validation**: Tests file handling and validation
- **Token Expiration**: Tests token expiration detection
- **Token Refresh**: Tests token refresh scenarios
- **File Sharing**: Tests permission creation logic
- **Error Handling**: Tests error scenarios
- **File Metadata**: Tests file information extraction

### Feature Tests (`tests/Feature/GoogleDriveServiceFeatureTest.php`)

Feature tests focus on integration and workflow testing:

- **Complete Workflow**: Tests the entire upload process
- **User Authentication**: Tests user token requirements
- **File Types**: Tests different file type handling
- **Integration Points**: Tests how the service integrates with the application

## Running Tests

### Prerequisites

1. Ensure you have PHPUnit installed:
```bash
composer require --dev phpunit/phpunit
```

2. Make sure your database is configured for testing (SQLite in memory is recommended)

### Running All Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run only GoogleDriveService tests
./vendor/bin/phpunit --filter=GoogleDriveService
```

### Running Specific Test Suites

```bash
# Run only unit tests
./vendor/bin/phpunit --testsuite=Unit

# Run only feature tests
./vendor/bin/phpunit --testsuite=Feature
```

### Using the Test Runner Script

```bash
# Make the script executable
chmod +x test-google-drive.sh

# Run the tests
./test-google-drive.sh
```

## Test Scenarios Covered

### 1. User Authentication
- ✅ User with valid Google tokens
- ✅ User without Google tokens
- ✅ Token expiration detection
- ✅ Token refresh handling

### 2. File Upload
- ✅ Valid file uploads
- ✅ Different file types (PDF, images, text)
- ✅ File size validation
- ✅ File metadata extraction

### 3. File Sharing
- ✅ Email-based sharing
- ✅ Public link sharing
- ✅ Permission creation

### 4. Error Handling
- ✅ Invalid tokens
- ✅ Missing credentials
- ✅ File validation errors

## Mocking Strategy

Since the tests don't require actual Google API credentials, we use:

1. **Laravel's UploadedFile::fake()** for file testing
2. **Database factories** for user creation
3. **Carbon timestamps** for token expiration testing
4. **Assertion testing** for logic validation

## Test Data

The tests use:
- **Test Users**: Created with `User::factory()`
- **Test Files**: Created with `UploadedFile::fake()`
- **Test Tokens**: Mock Google access and refresh tokens
- **Test Timestamps**: Using Carbon for time-based testing

## Adding New Tests

When adding new tests:

1. **Unit Tests**: Add to `tests/Unit/GoogleDriveServiceTest.php`
2. **Feature Tests**: Add to `tests/Feature/GoogleDriveServiceFeatureTest.php`
3. **Follow naming convention**: `test_descriptive_name()`
4. **Use proper assertions**: Test specific behaviors
5. **Mock external dependencies**: Don't rely on real API calls

## Example Test Structure

```php
public function test_new_feature()
{
    // Arrange - Set up test data
    $user = User::factory()->create([
        'google_access_token' => 'test_token',
    ]);
    $file = UploadedFile::fake()->create('test.pdf', 100);

    // Act - Perform the action being tested
    $result = $this->googleDriveService->someMethod($file, $user);

    // Assert - Verify the expected outcome
    $this->assertNotNull($result);
    $this->assertEquals('expected_value', $result);
}
```

## Troubleshooting

### Common Issues

1. **Database Connection**: Ensure SQLite is configured for testing
2. **Missing Dependencies**: Run `composer install` to install test dependencies
3. **Permission Issues**: Make sure test files are writable
4. **Environment Variables**: Ensure testing environment is properly configured

### Debugging Tests

```bash
# Run tests with verbose output
./vendor/bin/phpunit --verbose

# Run a specific test method
./vendor/bin/phpunit --filter=test_specific_method

# Run tests with coverage (if available)
./vendor/bin/phpunit --coverage-text
```

## Continuous Integration

For CI/CD pipelines, ensure:

1. **Test Environment**: Configure testing database
2. **Dependencies**: Install all required packages
3. **Test Execution**: Run tests before deployment
4. **Coverage Reports**: Generate and store coverage reports

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Descriptive Names**: Use clear, descriptive test method names
3. **Arrange-Act-Assert**: Follow the AAA pattern
4. **Mock External Services**: Don't rely on external APIs in tests
5. **Test Edge Cases**: Include boundary and error conditions
6. **Keep Tests Fast**: Avoid slow operations in tests
7. **Maintain Test Data**: Keep test data realistic and minimal 