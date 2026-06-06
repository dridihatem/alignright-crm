<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'photo',
        'doctor_id',
        'code_parrent',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'phone',
        'address',
        'specialization',
        'license_number',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function casesTechnician()
    {
        return $this->hasMany(CasePatient::class, 'technician_id');
    }

    public function casesLaboratory()
    {
        return $this->hasMany(CasePatient::class, 'laboratory_id');
    }

    public function cases()
    {
        return $this->hasMany(CasePatient::class, 'doctor_id');
    }

    public function laboratoryCases()
    {
        return $this->hasMany(CasePatient::class, 'laboratory_id');
    }

    public function technicianCases()
    {
        return $this->hasMany(CasePatient::class, 'technician_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin()
    {
        return $this->role->name === 'admin';
    }

    public function isDoctor()
    {
        return $this->role->name === 'doctor';
    }

    public function isTechnician()
    {
        return $this->role->name === 'technician';
    }

    public function isLaboratory()
    {
        return $this->role->name === 'laboratory';
    }

    public function isCommercial()
    {
        return $this->role->name === 'commercial';
    }

    // Status related methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }

    public function suspend()
    {
        $this->update(['status' => 'suspended']);
    }

    public function setPending()
    {
        $this->update(['status' => 'pending']);
    }

    /**
     * Get the correct photo URL
     */
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return asset('assets/img/avatars/default.png');
        }

        $photo = $this->photo;

        // Normalize any stored "/storage/..." value (even a full URL from another
        // domain/environment) to an absolute link on the current domain.
        if (\Illuminate\Support\Str::contains($photo, '/storage/')) {
            $relative = ltrim(\Illuminate\Support\Str::after($photo, '/storage/'), '/');
            return asset('storage/' . $relative);
        }

        // Already an absolute external URL we don't manage.
        if (\Illuminate\Support\Str::startsWith($photo, ['http://', 'https://'])) {
            return $photo;
        }

        // Bare filename or relative path.
        $relative = ltrim(\Illuminate\Support\Str::startsWith($photo, 'profile-photos/')
            ? $photo
            : 'profile-photos/' . $photo, '/');

        return asset('storage/' . $relative);
    }

            
        // Relation personnalisée si tu veux relier un docteur à son technicien et labo :
        public function technician()
        {
            return $this->hasOne(User::class, 'technician_id')->where('role', 3);
        }

        public function laboratory()
        {
            return $this->hasOne(User::class, 'laboratory_id')->where('role', 4);
        }

      
}
