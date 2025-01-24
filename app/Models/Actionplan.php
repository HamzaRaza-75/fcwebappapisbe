<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([DescScope::class])]
class Actionplan extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'task_id',
        'task_file',
        'word_count',
        'worth',
        'revision',
        'action_plan_starting_datetime',
        'action_plan_submition_datetime',
        'submited_by',
    ];


    public function taskmilestones(): BelongsTo
    {
        return $this->belongsTo(TaskMilestone::class, 'task_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submited_by');
    }

    public function taskrevision(): HasMany
    {
        return $this->hasMany(TaskRevision::class, 'action_plan_id');
    }
}
