<x-app-layout>
    <x-slot name="title">{{ __('master.settings') }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.settings') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        {{ __('master.settings') }}
                    </h5>
                    <small class="text-muted">{{ __('master.system_configuration') }}</small>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Settings Navigation -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('master.settings_sections') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#general-settings" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                                <i class="fas fa-cog me-2"></i>
                                {{ __('master.general_settings') }}
                            </a>
                            <a href="#email-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="fas fa-envelope me-2"></i>
                                {{ __('master.email_settings') }}
                            </a>
                            <a href="#appearance-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="fas fa-palette me-2"></i>
                                {{ __('master.appearance_settings') }}
                            </a>
                            <a href="#google-drive-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="fab fa-google-drive me-2"></i>
                                {{ __('master.google_drive_settings') }}
                            </a>
                            <a href="#system-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="fas fa-server me-2"></i>
                                {{ __('master.system_settings') }}
                            </a>
                            <a href="#backup-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="fas fa-database me-2"></i>
                                {{ __('master.backup_settings') }}
                            </a>
                            <a href="#identifiers-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                                <i class="fas fa-id-card me-2"></i>
                                {{ __('master.identifiers_settings') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="col-md-9">
                <div class="tab-content">
                    <!-- General Settings -->
                    <div class="tab-pane fade show active" id="general-settings">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ __('master.general_settings') }}</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.settings.general.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_name" class="form-label">{{ __('master.site_name') }}</label>
                                                <input type="text" class="form-control" id="site_name" name="site_name" 
                                                       value="{{ old('site_name', $settings['site_name'] ?? 'Dental Clinic Management') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_description" class="form-label">{{ __('master.site_description') }}</label>
                                                <input type="text" class="form-control" id="site_description" name="site_description" 
                                                       value="{{ old('site_description', $settings['site_description'] ?? 'Professional dental clinic management system') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="timezone" class="form-label">{{ __('master.timezone') }}</label>
                                                <select class="form-select" id="timezone" name="timezone">
                                                    <option value="UTC" {{ ($settings['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                                    <option value="Europe/Paris" {{ ($settings['timezone'] ?? '') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                                                    <option value="America/New_York" {{ ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="date_format" class="form-label">{{ __('master.date_format') }}</label>
                                                <select class="form-select" id="date_format" name="date_format">
                                                    <option value="Y-m-d" {{ ($settings['date_format'] ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                                    <option value="d/m/Y" {{ ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                                    <option value="m/d/Y" {{ ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="currency" class="form-label">{{ __('master.currency') }}</label>
                                                <select class="form-select" id="currency" name="currency">
                                                    <option value="TND" {{ ($settings['currency'] ?? 'TND') == 'TND' ? 'selected' : '' }}>TND (Tunisian Dinar)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="language" class="form-label">{{ __('master.language') }}</label>
                                                <select class="form-select" id="language" name="language">
                                                    <option value="en" {{ ($settings['language'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                                    <option value="fr" {{ ($settings['language'] ?? '') == 'fr' ? 'selected' : '' }}>Français</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> {{ __('master.save_settings') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="tab-pane fade" id="email-settings">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ __('master.email_settings') }}</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.settings.email.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mail_host" class="form-label">{{ __('master.mail_host') }}</label>
                                                <input type="text" class="form-control" id="mail_host" name="mail_host" 
                                                       value="{{ old('mail_host', $settings['mail_host'] ?? 'smtp.gmail.com') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mail_port" class="form-label">{{ __('master.mail_port') }}</label>
                                                <input type="number" class="form-control" id="mail_port" name="mail_port" 
                                                       value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mail_username" class="form-label">{{ __('master.mail_username') }}</label>
                                                <input type="email" class="form-control" id="mail_username" name="mail_username" 
                                                       value="{{ old('mail_username', $settings['mail_username'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mail_password" class="form-label">{{ __('master.mail_password') }}</label>
                                                <input type="password" class="form-control" id="mail_password" name="mail_password" 
                                                       value="{{ old('mail_password', $settings['mail_password'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mail_encryption" class="form-label">{{ __('master.mail_encryption') }}</label>
                                                <select class="form-select" id="mail_encryption" name="mail_encryption">
                                                    <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                                    <option value="ssl" {{ ($settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mail_from_address" class="form-label">{{ __('master.mail_from_address') }}</label>
                                                <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" 
                                                       value="{{ old('mail_from_address', $settings['mail_from_address'] ?? 'noreply@example.com') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="mail_from_name" class="form-label">{{ __('master.mail_from_name') }}</label>
                                        <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" 
                                               value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'Dental Clinic') }}">
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> {{ __('master.save_settings') }}
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="testEmail()">
                                        <i class="fas fa-paper-plane me-1"></i> {{ __('master.test_email') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Appearance Settings -->
                    <div class="tab-pane fade" id="appearance-settings">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ __('master.appearance_settings') }}</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.settings.appearance.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_logo" class="form-label">{{ __('master.site_logo') }}</label>
                                                <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                                                <small class="form-text text-muted">{{ __('master.logo_requirements') }}</small>
                                                
                                                @if($settings['site_logo'] ?? null)
                                                    <div class="mt-2">
                                                        <label class="form-label">{{ __('master.current_logo') }}:</label>
                                                        <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Current Logo" class="img-thumbnail" style="max-height: 60px;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="favicon" class="form-label">{{ __('master.favicon') }}</label>
                                                <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                                                <small class="form-text text-muted">{{ __('master.favicon_requirements') }}</small>
                                                
                                                @if($settings['favicon'] ?? null)
                                                    <div class="mt-2">
                                                        <label class="form-label">{{ __('master.current_favicon') }}:</label>
                                                        <img src="{{ asset('storage/' . $settings['favicon']) }}" alt="Current Favicon" class="img-thumbnail" style="max-height: 32px;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="primary_color" class="form-label">{{ __('master.primary_color') }}</label>
                                                <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" 
                                                       value="{{ old('primary_color', $settings['primary_color'] ?? '#696cff') }}" title="Choose primary color">
                                                <small class="form-text text-muted">{{ __('master.primary_color_description') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> {{ __('master.save_settings') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Google Drive Settings -->
                    <div class="tab-pane fade" id="google-drive-settings">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ __('master.google_drive_settings') }}</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.settings.google-drive.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="google_client_id" class="form-label">{{ __('master.google_client_id') }}</label>
                                                <input type="text" class="form-control" id="google_client_id" name="google_client_id" 
                                                       value="{{ old('google_client_id', $settings['google_client_id'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="google_client_secret" class="form-label">{{ __('master.google_client_secret') }}</label>
                                                <input type="password" class="form-control" id="google_client_secret" name="google_client_secret" 
                                                       value="{{ old('google_client_secret', $settings['google_client_secret'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="google_redirect_uri" class="form-label">{{ __('master.google_redirect_uri') }}</label>
                                                <input type="url" class="form-control" id="google_redirect_uri" name="google_redirect_uri" 
                                                       value="{{ old('google_redirect_uri', $settings['google_redirect_uri'] ?? route('google.callback')) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="google_folder_id" class="form-label">{{ __('master.google_folder_id') }}</label>
                                                <input type="text" class="form-control" id="google_folder_id" name="google_folder_id" 
                                                       value="{{ old('google_folder_id', $settings['google_folder_id'] ?? '') }}">
                                                <small class="form-text text-muted">{{ __('master.google_folder_id_help') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="google_drive_enabled" name="google_drive_enabled" 
                                                           value="1" {{ ($settings['google_drive_enabled'] ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="google_drive_enabled">
                                                        {{ __('master.enable_google_drive') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="default_upload_storage" class="form-label">{{ __('master.default_upload_storage') }}</label>
                                                <select class="form-select" id="default_upload_storage" name="default_upload_storage">
                                                    <option value="local" {{ ($settings['default_upload_storage'] ?? 'local') === 'local' ? 'selected' : '' }}>
                                                        {{ __('master.local_storage') }}
                                                    </option>
                                                    <option value="google_drive" {{ ($settings['default_upload_storage'] ?? 'local') === 'google_drive' ? 'selected' : '' }}>
                                                        {{ __('master.google_drive_storage') }}
                                                    </option>
                                                </select>
                                                <small class="form-text text-muted">{{ __('master.choose_default_storage_for_uploads') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> {{ __('master.save_settings') }}
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="testGoogleDrive()">
                                        <i class="fab fa-google-drive me-1"></i> {{ __('master.test_google_drive') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- System Settings -->
                    <div class="tab-pane fade" id="system-settings">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ __('master.system_settings') }}</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.settings.system.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_file_size" class="form-label">{{ __('master.max_file_size') }} (MB)</label>
                                                <input type="number" class="form-control" id="max_file_size" name="max_file_size" 
                                                       value="{{ old('max_file_size', $settings['max_file_size'] ?? '10') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="session_timeout" class="form-label">{{ __('master.session_timeout') }} (minutes)</label>
                                                <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                                                       value="{{ old('session_timeout', $settings['session_timeout'] ?? '120') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="pagination_limit" class="form-label">{{ __('master.pagination_limit') }}</label>
                                                <select class="form-select" id="pagination_limit" name="pagination_limit">
                                                    <option value="10" {{ ($settings['pagination_limit'] ?? '10') == '10' ? 'selected' : '' }}>10</option>
                                                    <option value="25" {{ ($settings['pagination_limit'] ?? '') == '25' ? 'selected' : '' }}>25</option>
                                                    <option value="50" {{ ($settings['pagination_limit'] ?? '') == '50' ? 'selected' : '' }}>50</option>
                                                    <option value="100" {{ ($settings['pagination_limit'] ?? '') == '100' ? 'selected' : '' }}>100</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="maintenance_mode" class="form-label">{{ __('master.maintenance_mode') }}</label>
                                                <select class="form-select" id="maintenance_mode" name="maintenance_mode">
                                                    <option value="0" {{ ($settings['maintenance_mode'] ?? '0') == '0' ? 'selected' : '' }}>{{ __('master.disabled') }}</option>
                                                    <option value="1" {{ ($settings['maintenance_mode'] ?? '') == '1' ? 'selected' : '' }}>{{ __('master.enabled') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="debug_mode" name="debug_mode" 
                                                   value="1" {{ ($settings['debug_mode'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="debug_mode">
                                                {{ __('master.debug_mode') }}
                                            </label>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> {{ __('master.save_settings') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Settings -->
                    <div class="tab-pane fade" id="backup-settings">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ __('master.backup_settings') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-database fa-3x text-primary mb-3"></i>
                                                <h5>{{ __('master.database_backup') }}</h5>
                                                <p class="text-muted">{{ __('master.create_database_backup') }}</p>
                                                <button type="button" class="btn btn-primary" onclick="createBackup()">
                                                    <i class="fas fa-download me-1"></i> {{ __('master.create_backup') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-upload fa-3x text-success mb-3"></i>
                                                <h5>{{ __('master.restore_backup') }}</h5>
                                                <p class="text-muted">{{ __('master.restore_from_backup') }}</p>
                                                <button type="button" class="btn btn-success" onclick="restoreBackup()">
                                                    <i class="fas fa-upload me-1"></i> {{ __('master.restore_backup') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <h6>{{ __('master.backup_history') }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="backup-history-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('master.backup_name') }}</th>
                                                    <th>{{ __('master.created_at') }}</th>
                                                    <th>{{ __('master.size') }}</th>
                                                    <th>{{ __('master.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">
                                                        {{ __('master.no_backups_found') }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Identifiers Settings -->
                    <div class="tab-pane fade" id="identifiers-settings">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ __('master.identifiers_settings') }}</h6>
                                <small class="text-muted">{{ __('master.identifiers_settings_desc') }}</small>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning d-flex align-items-start" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                    <div>{{ __('master.regenerate_identifiers_warning') }}</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-folder-open fa-3x text-primary mb-3"></i>
                                                <h5>{{ __('master.regenerate_case_ids') }}</h5>
                                                <p class="text-muted mb-1">{{ __('master.case_id_format') }}: <code>AR-####</code></p>
                                                <p class="text-muted small mb-3">{{ $identifierStats['cases'] ?? 0 }} {{ __('master.cases') }}</p>
                                                <button type="button" class="btn btn-primary" onclick="regenerateIdentifiers('cases', this)">
                                                    <i class="fas fa-sync-alt me-1"></i> {{ __('master.regenerate') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-user-tag fa-3x text-success mb-3"></i>
                                                <h5>{{ __('master.regenerate_patient_references') }}</h5>
                                                <p class="text-muted mb-1">{{ __('master.reference') }}: <code>PT-####</code></p>
                                                <p class="text-muted small mb-3">{{ $identifierStats['patients'] ?? 0 }} {{ __('master.patients') }}</p>
                                                <button type="button" class="btn btn-success" onclick="regenerateIdentifiers('patients', this)">
                                                    <i class="fas fa-sync-alt me-1"></i> {{ __('master.regenerate') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-layer-group fa-3x text-info mb-3"></i>
                                                <h5>{{ __('master.regenerate_all_identifiers') }}</h5>
                                                <p class="text-muted mb-1"><code>AR-####</code> + <code>PT-####</code></p>
                                                <p class="text-muted small mb-3">{{ __('master.cases_and_patients') }}</p>
                                                <button type="button" class="btn btn-info text-white" onclick="regenerateIdentifiers('both', this)">
                                                    <i class="fas fa-sync-alt me-1"></i> {{ __('master.regenerate') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Regenerate case IDs / patient references
        function regenerateIdentifiers(target, button) {
            const messages = {
                cases: '{{ __('master.confirm_regenerate_case_ids') }}',
                patients: '{{ __('master.confirm_regenerate_patient_references') }}',
                both: '{{ __('master.confirm_regenerate_all_identifiers') }}'
            };

            if (!confirm(messages[target] || messages.both)) {
                return;
            }

            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __('master.processing') }}';

            fetch('{{ route("admin.settings.regenerate-identifiers") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ target: target })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message + ' (' + data.cases + ' / ' + data.patients + ')');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    toastr.error(data.message || '{{ __('master.error_regenerating_identifiers') }}');
                }
            })
            .catch(error => {
                toastr.error('{{ __('master.error_regenerating_identifiers') }}');
                console.error('Error:', error);
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        // Test Email Configuration
        function testEmail() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Testing...';
            
            fetch('{{ route("admin.settings.test-email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('An error occurred while testing email configuration');
                console.error('Error:', error);
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        // Test Google Drive Connection
        function testGoogleDrive() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Testing...';
            
            fetch('{{ route("admin.settings.test-google-drive") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('An error occurred while testing Google Drive connection');
                console.error('Error:', error);
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        // Create Database Backup
        function createBackup() {
            if (confirm('{{ __("master.confirm_create_backup") }}')) {
                const button = event.target;
                const originalText = button.innerHTML;
                
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creating...';
                
                fetch('{{ route("admin.settings.create-backup") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        // Refresh backup history
                        loadBackupHistory();
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    toastr.error('An error occurred while creating backup');
                    console.error('Error:', error);
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
            }
        }

        // Restore Database Backup
        function restoreBackup() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.sql';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (confirm('{{ __("master.confirm_restore_backup") }}')) {
                        const formData = new FormData();
                        formData.append('backup_file', file);
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                        
                        const button = event.target;
                        const originalText = button.innerHTML;
                        
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Restoring...';
                        
                        fetch('{{ route("admin.settings.restore-backup") }}', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                toastr.success(data.message);
                            } else {
                                toastr.error(data.message);
                            }
                        })
                        .catch(error => {
                            toastr.error('An error occurred while restoring backup');
                            console.error('Error:', error);
                        })
                        .finally(() => {
                            button.disabled = false;
                            button.innerHTML = originalText;
                        });
                    }
                }
            };
            input.click();
        }

        // Load Backup History
        function loadBackupHistory() {
            fetch('{{ route("admin.settings.backup-history") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBackupHistoryTable(data.data);
                } else {
                    console.error('Failed to load backup history:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading backup history:', error);
            });
        }

        // Update Backup History Table
        function updateBackupHistoryTable(backups) {
            const tbody = document.querySelector('#backup-history-table tbody');
            
            if (backups.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            {{ __('master.no_backups_found') }}
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = backups.map(backup => `
                    <tr>
                        <td>${backup.filename}</td>
                        <td>${backup.created_at}</td>
                        <td>${backup.size}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="downloadBackup('${backup.filename}')">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBackup('${backup.filename}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }
        function downloadBackup(filename) {
            window.location.href = `{{ route('admin.settings.download-backup', '') }}/${filename}`;
        }

        // Delete Backup
        function deleteBackup(filename) {
            if (confirm('Are you sure you want to delete this backup?')) {
                fetch(`{{ route('admin.settings.delete-backup', '') }}/${filename}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        loadBackupHistory();
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    toastr.error('An error occurred while deleting backup');
                    console.error('Error:', error);
                });
            }
        }

        // Load backup history when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadBackupHistory();
        });
    </script>
    @endpush
</x-app-layout>