<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskRemarks extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['task_id', 'grade', 'remarks'];


    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
