# System Architecture - SaaS Doctor Dentiste

## 🏗️ Architecture Overview

SaaS Doctor Dentiste is built on a modern, scalable web application architecture using Laravel PHP framework, following best practices for security, performance, and maintainability.

## 🎯 Architecture Principles

### Design Principles
- **Separation of Concerns** - Clear separation between presentation, business logic, and data layers
- **Scalability** - Designed to handle growing user base and data volume
- **Security First** - Security considerations integrated throughout the architecture
- **Maintainability** - Clean, documented code following industry standards
- **Performance** - Optimized for fast response times and efficient resource usage

### Architectural Patterns
- **MVC (Model-View-Controller)** - Laravel's MVC pattern for organized code structure
- **Repository Pattern** - Data access abstraction for flexibility
- **Service Layer Pattern** - Business logic encapsulation in service classes
- **Observer Pattern** - Event-driven architecture for notifications and logging
- **Factory Pattern** - Object creation abstraction for testing and flexibility

## 🏢 High-Level Architecture

### System Components
```
┌─────────────────────────────────────────────────────────────┐
│                     Client Layer                            │
├─────────────────────────────────────────────────────────────┤
│  Web Browser  │  Mobile Browser  │  API Clients (Future)   │
└─────────────────────────────────────────────────────────────┘
                            │
                     HTTPS/TLS
                            │
┌─────────────────────────────────────────────────────────────┐
│                   Load Balancer                             │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                 Web Application Layer                       │
├─────────────────────────────────────────────────────────────┤
│  Controllers  │  Middleware  │  Views  │  Routes           │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                 Business Logic Layer                        │
├─────────────────────────────────────────────────────────────┤
│  Services  │  Repositories  │  Models  │  Events           │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                    Data Layer                               │
├─────────────────────────────────────────────────────────────┤
│  MySQL Database  │  File Storage  │  Cache  │  Sessions    │
└─────────────────────────────────────────────────────────────┘
```

## 📱 Application Architecture

### Laravel Framework Structure
```
app/
├── Console/              # Artisan commands
├── Events/              # Application events
├── Exceptions/          # Exception handling
├── Helpers/             # Helper functions and utilities
├── Http/
│   ├── Controllers/     # Application controllers
│   ├── Middleware/      # HTTP middleware
│   └── Requests/        # Form request validation
├── Jobs/                # Queue jobs
├── Listeners/           # Event listeners
├── Mail/                # Email templates and logic
├── Models/              # Eloquent models
├── Notifications/       # Notification classes
├── Policies/            # Authorization policies
├── Providers/           # Service providers
├── Rules/               # Custom validation rules
└── Services/            # Business logic services
```

### Controller Architecture

#### Admin Controllers
```php
AdminController.php              # Main admin functionality
├── User Management             # Create, edit, delete users
├── Case Management            # Monitor all cases
├── System Settings           # Configure system settings
├── Invoice Management        # Handle billing operations
└── Analytics & Reporting     # System analytics

Admin/
├── AdminPriceManagerController.php    # Price management
└── AdminDashboardController.php       # Admin dashboard
```

#### Doctor Controllers
```php
DoctorsController.php           # Main doctor functionality
├── Patient Management         # CRUD operations for patients
├── Case Management           # Create and manage cases
├── Treatment Plan Approval   # Approve/reject treatment plans
├── File Management          # Google Drive integration
└── Calendar Management      # Appointments and scheduling

Doctor/
├── DoctorCaseController.php           # Doctor case operations
├── DoctorPatientController.php        # Patient management
├── DoctorTreatmentPlanController.php  # Treatment plan handling
└── DoctorDashboardController.php      # Doctor dashboard
```

#### Technician & Laboratory Controllers
```php
TechniciansController.php       # Technician functionality
├── Case Processing            # Handle assigned cases
├── Treatment Plan Creation    # Create treatment plans
├── File Upload & Sharing     # Share work files
└── Communication            # Comments and updates

LaboratoryController.php        # Laboratory functionality
├── Order Management          # Process manufacturing orders
├── Status Updates           # Update production status
├── Quality Control         # Final quality checks
└── Delivery Management     # Handle shipments
```

### Service Layer Architecture

#### Core Services
```php
Services/
├── UserService.php             # User management operations
├── CaseService.php             # Case workflow management
├── TreatmentPlanService.php    # Treatment plan operations
├── FileService.php             # File handling and storage
├── NotificationService.php     # Notification dispatch
├── InvoiceService.php          # Invoice generation
├── EmailService.php            # Email operations
└── GoogleDriveService.php      # Google Drive integration
```

#### Service Responsibilities
- **UserService**: User creation, authentication, role management
- **CaseService**: Case lifecycle management, status transitions
- **TreatmentPlanService**: Plan creation, approval workflow
- **FileService**: File upload, organization, security
- **NotificationService**: Multi-channel notification dispatch
- **InvoiceService**: Automated invoice generation and management

### Model Architecture

#### Core Models
```php
Models/
├── User.php                    # User accounts and authentication
├── Case.php                    # Case management
├── Patient.php                 # Patient information
├── TreatmentPlan.php          # Treatment plans
├── Invoice.php                # Billing and invoices
├── Notification.php           # System notifications
├── Comment.php                # Case comments
├── File.php                   # File metadata
└── Setting.php                # System settings
```

#### Model Relationships
```php
User (1) ──────── (∞) Case
Case (1) ──────── (1) TreatmentPlan
Case (1) ──────── (∞) Comment
Case (1) ──────── (∞) File
User (1) ──────── (∞) Notification
Case (1) ──────── (1) Invoice
Patient (1) ──── (∞) Case
```

## 🗄️ Database Architecture

### Database Schema Design

#### Core Tables
```sql
-- Users table with role-based access
users
├── id (Primary Key)
├── name
├── email (Unique)
├── role (admin, doctor, technician, laboratory)
├── password_hash
├── email_verified_at
├── created_at
└── updated_at

-- Cases table for case management
cases
├── id (Primary Key)
├── patient_id (Foreign Key)
├── doctor_id (Foreign Key)
├── technician_id (Foreign Key)
├── laboratory_id (Foreign Key)
├── case_type
├── status
├── priority
├── description
├── special_instructions
├── deadline
├── created_at
└── updated_at
```

#### Relationship Tables
```sql
-- Treatment plans
treatment_plans
├── id (Primary Key)
├── case_id (Foreign Key)
├── technician_id (Foreign Key)
├── description
├── materials
├── cost_estimate
├── timeline
├── status (pending, approved, rejected)
├── created_at
└── updated_at

-- Notifications
notifications
├── id (Primary Key)
├── user_id (Foreign Key)
├── case_id (Foreign Key)
├── title
├── message
├── type
├── read_at
├── created_at
└── updated_at
```

### Database Optimization

#### Indexing Strategy
```sql
-- Performance-critical indexes
CREATE INDEX idx_cases_status ON cases(status);
CREATE INDEX idx_cases_doctor_id ON cases(doctor_id);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_treatment_plans_case_id ON treatment_plans(case_id);
CREATE INDEX idx_users_role ON users(role);
```

#### Query Optimization
- **Eager Loading** - Use Eloquent relationships to prevent N+1 queries
- **Database Indexes** - Strategic indexing on frequently queried columns
- **Query Caching** - Cache expensive queries using Laravel's cache system
- **Database Connection Pooling** - Efficient database connection management

## 🔐 Security Architecture

### Authentication & Authorization

#### Multi-Role Authentication
```php
// Role-based access control
public function authorize(Request $request)
{
    return match($request->user()->role) {
        'admin' => $this->authorizeAdmin($request),
        'doctor' => $this->authorizeDoctor($request),
        'technician' => $this->authorizeTechnician($request),
        'laboratory' => $this->authorizeLaboratory($request),
        default => false
    };
}
```

#### Security Middleware Stack
```php
Middleware Pipeline:
1. TrustProxies          # Proxy trust configuration
2. CheckForMaintenanceMode # Maintenance mode handling
3. ValidatePostSize      # Post size validation
4. ConvertEmptyStrings   # Empty string conversion
5. TrimStrings          # String trimming
6. Authenticate         # User authentication
7. CheckRole           # Role-based authorization
8. VerifyCsrfToken     # CSRF protection
9. SubstituteBindings  # Route model binding
```

### Data Protection

#### Encryption
- **Data at Rest** - Database encryption using Laravel's encryption
- **Data in Transit** - HTTPS/TLS encryption for all communications
- **File Encryption** - Sensitive files encrypted before storage
- **Password Hashing** - Bcrypt hashing for user passwords

#### Privacy Controls
- **Data Access Logging** - All data access logged for audit
- **User Permissions** - Granular permissions for data access
- **Data Retention** - Configurable data retention policies
- **GDPR Compliance** - Privacy controls for data protection

## 🔄 Integration Architecture

### Google Drive Integration

#### Service Architecture
```php
class GoogleDriveService
{
    private GoogleClient $client;
    private DriveService $service;
    
    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setAuthConfig(config('services.google'));
        $this->service = new DriveService($this->client);
    }
    
    public function createCaseFolder(Case $case): string
    {
        // Create organized folder structure
        return $this->service->files->create($folderMetadata);
    }
}
```

#### File Organization Strategy
```
Google Drive Structure:
/SaaS-Doctor-Dentiste/
├── Users/
│   ├── Doctors/
│   │   └── [Doctor-ID]/
│   ├── Technicians/
│   │   └── [Technician-ID]/
│   └── Laboratories/
│       └── [Laboratory-ID]/
├── Cases/
│   └── [Year]/
│       └── [Month]/
│           └── [Case-ID]/
└── Archive/
    └── [Year]/
```

### Email Integration

#### Email Service Architecture
```php
class EmailService
{
    public function sendNotification(User $user, Notification $notification)
    {
        Mail::to($user->email)->send(
            new NotificationMail($notification)
        );
    }
    
    public function sendBulkNotifications(Collection $users, string $template)
    {
        Mail::queue(new BulkNotificationMail($users, $template));
    }
}
```

## 📈 Performance Architecture

### Caching Strategy

#### Multi-Level Caching
```php
Cache Layers:
1. Application Cache (Redis/Memcached)
   ├── User sessions
   ├── Frequently accessed data
   └── Query results

2. Database Query Cache
   ├── Expensive queries
   ├── Aggregated data
   └── Report data

3. File System Cache
   ├── Compiled views
   ├── Configuration cache
   └── Route cache
```

#### Cache Implementation
```php
// Service-level caching
public function getUserCases(User $user): Collection
{
    return Cache::remember(
        "user.{$user->id}.cases",
        now()->addMinutes(30),
        fn() => $user->cases()->with('patient', 'treatmentPlan')->get()
    );
}
```

### Database Performance

#### Query Optimization
```php
// Efficient query patterns
public function getDashboardData(User $user): array
{
    return [
        'active_cases' => $user->cases()
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count(),
        'pending_approvals' => $user->treatmentPlans()
            ->where('status', 'pending')
            ->count(),
        'recent_activity' => $user->notifications()
            ->latest()
            ->limit(5)
            ->get()
    ];
}
```

### Asset Optimization

#### Frontend Performance
```php
// Asset pipeline optimization
public function buildAssets(): void
{
    // CSS optimization
    $css = [
        'vendor/bootstrap/bootstrap.min.css',
        'assets/css/demo.css',
        'custom/application.css'
    ];
    
    // JavaScript optimization
    $js = [
        'vendor/jquery/jquery.min.js',
        'vendor/bootstrap/bootstrap.bundle.min.js',
        'assets/js/main.js'
    ];
    
    $this->minifyAndCombine($css, $js);
}
```

## 🔧 Development Architecture

### Code Organization

#### Service Provider Architecture
```php
// Application service providers
AppServiceProvider       # Core application bindings
AuthServiceProvider     # Authentication services
BroadcastServiceProvider # Broadcasting services
EventServiceProvider   # Event listeners
RouteServiceProvider   # Route definitions
```

#### Event-Driven Architecture
```php
// Event system for decoupled functionality
Events:
├── CaseCreated          # Trigger notifications
├── TreatmentPlanSubmitted # Approval workflow
├── InvoiceGenerated     # Payment processing
├── UserRegistered       # Welcome email
└── FileUploaded         # File processing
```

### Testing Architecture

#### Test Structure
```php
tests/
├── Feature/             # Integration tests
│   ├── CaseManagementTest.php
│   ├── UserAuthenticationTest.php
│   └── InvoiceGenerationTest.php
├── Unit/                # Unit tests
│   ├── Services/
│   ├── Models/
│   └── Helpers/
└── Browser/             # Browser tests (Laravel Dusk)
    ├── LoginTest.php
    └── CaseWorkflowTest.php
```

## 🚀 Deployment Architecture

### Environment Configuration

#### Environment Separation
```php
Environments:
├── Development          # Local development environment
├── Staging             # Pre-production testing
├── Production          # Live production system
└── Testing             # Automated testing environment
```

#### Configuration Management
```php
// Environment-specific configuration
config/
├── app.php             # Application configuration
├── database.php        # Database connections
├── mail.php           # Email configuration
├── services.php       # Third-party services
└── cache.php          # Caching configuration
```

### Scalability Considerations

#### Horizontal Scaling
- **Load Balancer** - Distribute traffic across multiple servers
- **Database Replication** - Master-slave database setup
- **File Storage** - Distributed file storage system
- **Session Storage** - Redis-based session management
- **Queue Workers** - Background job processing

#### Monitoring & Logging
```php
// Comprehensive logging strategy
Log Channels:
├── Application Logs     # General application events
├── Security Logs       # Authentication and authorization
├── Performance Logs    # Query performance and timing
├── Error Logs         # Exception and error tracking
└── User Activity Logs # User action tracking
```

## 📋 Architecture Best Practices

### Code Quality
- **PSR Standards** - Follow PHP-FIG standards
- **SOLID Principles** - Single responsibility, open/closed, etc.
- **Clean Code** - Readable, maintainable code
- **Documentation** - Comprehensive code documentation
- **Testing** - Unit, integration, and browser testing

### Security Best Practices
- **Input Validation** - Validate all user input
- **Output Encoding** - Encode output to prevent XSS
- **SQL Injection Prevention** - Use parameterized queries
- **CSRF Protection** - Protect against cross-site request forgery
- **Rate Limiting** - Prevent abuse and DoS attacks

### Performance Best Practices
- **Database Optimization** - Efficient queries and indexing
- **Caching Strategy** - Multi-level caching implementation
- **Asset Optimization** - Minification and compression
- **CDN Usage** - Content delivery network for static assets
- **Monitoring** - Continuous performance monitoring

---

**Architecture Version**: 1.0  
**Last Updated**: January 2024  
**Next Review**: March 2024

**Technical Contacts**:
- **Architecture Lead**: architecture@sassdoctordentiste.com
- **Development Team**: development@sassdoctordentiste.com
