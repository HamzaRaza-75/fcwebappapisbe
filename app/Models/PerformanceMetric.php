<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[ScopedBy([DescScope::class])]
class PerformanceMetric extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'evaluated_by',
        'evaluated_to',
        'punctuality',
        'response_time',
        'in_and_out',
        'team_spirit',
        'attitude_behavior',
        'cancellations_quiz_exam_performance',
        'formatting_referencing',
        'language_grip_it',
        'content_flow_quality',
        'logics_structure',
        'understanding_instructions',
        'expertise_technical_subjects',
        'advancement_language',
        'focused_new_learnings',
        'planning_management',
        'plagiarism',
        'ai_tools_usage',
        'flawed_language_it',
        'coursework_missed',
    ];
}
