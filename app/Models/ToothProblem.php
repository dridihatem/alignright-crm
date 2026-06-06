<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToothProblem extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function cases()
    {
        return $this->hasMany(CasePatient::class);
    }

    public function tooth_problem_cases()
    {
        return $this->hasMany(ToothProblemCase::class, 'tooth_problem_id');
    }
}
