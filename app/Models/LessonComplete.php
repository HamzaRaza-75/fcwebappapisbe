<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonComplete extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'watched_at',
    ];
}
