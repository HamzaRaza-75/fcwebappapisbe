<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[ScopedBy([DescScope::class])]
class TaskMilestone extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'assigned_to',
        'assigned_by',
        'task_milestone_name',
        'task_milestone_description',
        'task_milestone_file',
        'status',
        'worth',
        'word_count',
        'deadline_date',
        'completed_date',
        'seen_at',
    ];

    // scopes starts here
    public function scopeAssigneduser(Builder $query): void
    {
        $query->where('assigned_to', Auth::user()->id);
    }


    // scopes ends here


    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function actionplan(): HasMany
    {
        return $this->hasMany(Actionplan::class, 'task_id');
    }
}
