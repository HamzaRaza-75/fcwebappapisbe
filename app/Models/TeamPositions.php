<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[ScopedBy([DescScope::class])]
class TeamPositions extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['position_name', 'team_id'];


    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function teamrequest(): HasMany
    {
        return $this->hasMany(TeamRequest::class);
    }
}
