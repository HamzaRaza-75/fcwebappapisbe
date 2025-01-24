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
class Attendence extends Model
{
    use HasFactory, SoftDeletes, HasUuids;


    protected $fillable = ['user_id', 'employe_check_in', 'employe_check_out', 'absent'];

    public function userid()
    {
        return $this->user_id;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
