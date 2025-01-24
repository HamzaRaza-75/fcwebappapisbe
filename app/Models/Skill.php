<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[ScopedBy([DescScope::class])]
class Skill extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['skills_tags', 'user_id'];


    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
