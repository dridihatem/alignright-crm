<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToothProblemCase extends Model
{
    protected $table = 'tooth_problem_cases';
    protected $fillable = [
        'case_id',
        'tooth_number',
        'tooth_problem_id',
        'tooth_notes',
    ];

    public function case()
    {
        return $this->belongsTo(CasePatient::class, 'case_id');
    }

    public function tooth_problem()
    {
        return $this->belongsTo(ToothProblem::class, 'tooth_problem_id');
    }

}
