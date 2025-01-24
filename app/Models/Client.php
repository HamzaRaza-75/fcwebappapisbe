<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ScopedBy([DescScope::class])]
class Client extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['name', 'countary_name'];


    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function tasksmilestones(): HasManyThrough
    {
        return $this->through('tasks')->has('taskmilestones');
    }

    public function latesttask(): HasOne
    {
        return $this->tasks()->latestOfMany();
    }
}
