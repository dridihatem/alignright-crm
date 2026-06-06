<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'reference',
        'name',
        'surname',
        'gender',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'birth_date',
        'photo',
        'date_of_birth',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'date_of_birth' => 'date',
    ];

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('assets/img/avatars/default.png');
    }

    public function getFullNameAttribute()
    {
        return trim($this->name . ' ' . $this->surname);
    }

    public function cases()
    {
        return $this->hasMany(CasePatient::class);
    }
}
