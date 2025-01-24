<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[ScopedBy([DescScope::class])]
class Shedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'shedule_name',
        'shedule_description',
        'end_time',
    ];

    public function scopeUsershedule(Builder $query): Void
    {
        $query->where('user_id', Auth::user()->id);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
