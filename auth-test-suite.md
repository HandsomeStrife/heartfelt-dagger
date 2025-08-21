# DaggerHeart Authentication Test Suite

This document outlines the comprehensive test suite created for the DaggerHeart authentication system.

## Test Coverage

### 1. **Feature Tests**

#### **Authentication Tests**
- **Registration Test** (`tests/Feature/Auth/RegistrationTest.php`)
  - Registration screen rendering
  - Successful user registration
  - Username/email/password validation
  - Unique username/email constraints
  - Password confirmation validation
  - Minimum password length requirements
  - Guest-only access to registration

- **Login Test** (`tests/Feature/Auth/LoginTest.php`)
  - Login screen rendering
  - Successful authentication
  - Invalid credential handling
  - Email/password validation
  - Remember me functionality
  - Guest-only access to login
  - Logout functionality

- **Route Protection Test** (`tests/Feature/Auth/RouteProtectionTest.php`)
  - Guest access to public routes
  - Authentication required for protected routes
  - Middleware redirection
  - Session persistence
  - CSRF protection
  - Legacy route compatibility

#### **Livewire Component Tests**
- **Login Component Test** (`tests/Feature/Livewire/Auth/LoginComponentTest.php`)
  - Component rendering
  - Property binding (email, password, remember)
  - Validation rules
  - Authentication logic
  - View content verification
  - Error handling

- **Register Component Test** (`tests/Feature/Livewire/Auth/RegisterComponentTest.php`)
  - Component rendering
  - Property binding (username, email, password, confirmation)
  - Validation rules
  - Registration logic
  - Automatic login after registration
  - View content verification

#### **Navigation Test** (`tests/Feature/NavigationTest.php`)
- Guest navigation links
- Authenticated user menu
- User avatar display
- Dropdown functionality
- Route accessibility
- Page-specific navigation visibility

#### **Integration Test** (`tests/Feature/Auth/IntegrationTest.php`)
- Complete registration-to-login flow
- Cross-request session persistence
- Remember me integration
- Error handling and recovery
- Route middleware integration

### 2. **Unit Tests**

#### **User Model Test** (`tests/Unit/UserModelTest.php`)
- Model creation and validation
- Fillable and hidden attributes
- Password hashing
- Attribute casting
- Factory functionality
- Database operations

## Running the Tests

### Run All Authentication Tests
```bash
# Laravel Sail (recommended)
./vendor/bin/sail php artisan test --testsuite=Feature --filter=Auth

# Or all tests
./vendor/bin/sail php artisan test

# Local PHP (if not using Docker)
php artisan test --testsuite=Feature --filter=Auth
```

### Run Specific Test Categories

```bash
# Registration tests only
./vendor/bin/sail php artisan test tests/Feature/Auth/RegistrationTest.php

# Login tests only
./vendor/bin/sail php artisan test tests/Feature/Auth/LoginTest.php

# Livewire component tests
./vendor/bin/sail php artisan test tests/Feature/Livewire/

# Unit tests
./vendor/bin/sail php artisan test tests/Unit/

# Integration tests
./vendor/bin/sail php artisan test tests/Feature/Auth/IntegrationTest.php
```

### Run with Coverage (if configured)
```bash
./vendor/bin/sail php artisan test --coverage --min=80
```

## Test Database Setup

The tests use Laravel's `RefreshDatabase` trait, which:
- Creates a fresh test database for each test
- Runs migrations automatically
- Cleans up after each test

## Test Scenarios Covered

### ✅ **Authentication Flow**
- [x] User registration with validation
- [x] User login with credentials
- [x] Password hashing and verification
- [x] Remember me functionality
- [x] Session management
- [x] Logout functionality

### ✅ **Security**
- [x] CSRF protection
- [x] Password minimum length
- [x] Email format validation
- [x] Unique username/email constraints
- [x] Guest middleware
- [x] Auth middleware

### ✅ **User Experience**
- [x] Navigation for guests vs authenticated users
- [x] Proper redirects after auth actions
- [x] Error message display
- [x] Loading states
- [x] Form validation feedback

### ✅ **Livewire Integration**
- [x] Component rendering
- [x] Property binding
- [x] Real-time validation
- [x] Form submission
- [x] Redirect handling

### ✅ **Route Protection**
- [x] Protected routes require authentication
- [x] Guest-only routes redirect authenticated users
- [x] Middleware proper functioning
- [x] Legacy route compatibility

## Test Data Factories

The `UserFactory` has been updated to work with the new schema:

```php
// Creates users with:
- username: unique fake username
- email: unique fake email
- password: hashed 'password' (default)
- email_verified_at: current timestamp
- remember_token: random 10-character string
```

## Assertion Examples

### Authentication Assertions
```php
$this->assertAuthenticated();
$this->assertGuest();
$this->assertAuthenticatedAs($user);
```

### Database Assertions
```php
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('users', ['username' => 'deleted_user']);
```

### Response Assertions
```php
$response->assertRedirect('/rooms');
$response->assertStatus(200);
$response->assertSessionHasErrors(['email']);
```

### Livewire Assertions
```php
Livewire::test(Login::class)
    ->set('email', 'test@example.com')
    ->assertSet('email', 'test@example.com')
    ->call('login')
    ->assertRedirect('/rooms');
```

## Continuous Integration

These tests are designed to run in CI/CD pipelines. Make sure your CI environment:

1. Has access to a test database
2. Runs `php artisan migrate` before tests
3. Has proper environment variables set
4. Includes Livewire testing dependencies

## Troubleshooting

### Common Issues

1. **Database connection errors**: Ensure test database is configured
2. **Livewire component not found**: Check component namespaces
3. **CSRF token mismatch**: Tests should handle CSRF automatically
4. **Session issues**: Use `RefreshDatabase` trait

### Debug Mode
```bash
# Run tests with verbose output
./vendor/bin/sail php artisan test --verbose

# Run specific test with debugging
./vendor/bin/sail php artisan test tests/Feature/Auth/LoginTest.php::test_users_can_authenticate_using_the_login_screen --verbose
```
