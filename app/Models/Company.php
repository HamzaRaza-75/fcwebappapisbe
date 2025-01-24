<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[ScopedBy([DescScope::class])]
class Company extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['company_name', 'slug', 'company_description', 'company_image'];


    public function team(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
