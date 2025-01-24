<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Team extends Model
{
    use HasFactory, SoftDeletes, HasUuids;


    protected $fillable = ['team_name', 'slug', 'description', 'team_image', 'company_id'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }


    public function teamrequest(): HasMany
    {
        return $this->hasMany(TeamRequest::class);
    }


    public function teamposition(): HasMany

    {
        return $this->hasMany(TeamPositions::class);
    }

    public function userteam(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_team');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function tasksmilestones(): HasManyThrough
    {
        return $this->hasManyThrough(
            TaskMilestone::class,
            Task::class,
            'team_id', // Foreign key on the environments table...
            'task_id', // Foreign key on the deployments table...
            'id', // Local key on the projects table...
            'id' // Local key on the environments table...
        );
    }
}
