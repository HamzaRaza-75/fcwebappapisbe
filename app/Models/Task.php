<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DescScope;
use App\Models\Scopes\ProjectScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[ScopedBy([DescScope::class])]
#[ScopedBy([ProjectScope::class])]
class Task extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'task_num',
        'created_by',
        'client_id',
        'team_id',
        'task_name',
        'task_description',
        'task_file',
        'status',
        'account',
        'estimated_budjet',
        'word_count',
        'starting_date',
        'deadline_date',
        'completed_date',
    ];

    public function taskmilestones(): HasMany
    {
        return $this->hasMany(TaskMilestone::class);
    }

    public function actionplanon(): HasManyThrough
    {
        return $this->through('taskmilestones')->has('actionplan');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
