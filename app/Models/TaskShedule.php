<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskShedule extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'task_id',
        'schedule_name',
        'schedule_date',
        'schedule_url',
        'schedule_description',
        'task_file',
    ];
}
