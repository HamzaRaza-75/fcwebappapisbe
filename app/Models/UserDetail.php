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
class UserDetail extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['user_id', 'phone_no', 'gurdian_name', 'gurdian_phone_no', 'CNIC_image', 'dateofbirth', 'gender', 'profile_image', 'current_address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
