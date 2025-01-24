<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([DescScope::class])]
class Course extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'creater_id',
        'course_name',
        'course_description',
        'platform',
        'status',
        'login',
        'password',
        'duration_in_days',
        'platform_url',
        'course_image',
    ];

    public function creater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creater_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function courserequest(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_requests')->using(CourseRequest::class);
    }
}
