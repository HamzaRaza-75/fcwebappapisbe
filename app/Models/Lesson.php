<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'course_id',
        'title',
        'content',
        'url',
        'should_completed_in',
    ];

    public function creater(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
