<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DescScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[ScopedBy([DescScope::class])]
class TaskRevision extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'action_plan_id',
        'revision_title',
        'revision_description',
        'revision_file',
        'seen_at',
        'deadline_date',
        'completed'
    ];

    public function actionplan(): BelongsTo
    {
        return $this->belongsTo(Actionplan::class, 'action_plan_id');
    }
}
