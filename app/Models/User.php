<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Scopes\DescScope;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use  HasFactory, Notifiable, HasRoles, HasUuids;

    protected $with = ['userdetail'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'teamposition_id',
        'status'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }


    // scopes starts here

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->getRoleNames(),
            'name' => $this->name,
            'email' => $this->email
        ];
    }



    // scopes starts here

    public function scopeTrueuser(Builder $query): void
    {
        $query->where('id', '!=', Auth::user()->id)
            ->where('status', 'active');
    }

    // scopes ends here


    // public function isNotBlocked()
    // {
    //     return $this->status !== "blocked";
    // }

    public function tasksAssignedTo(): HasMany
    {
        return $this->hasMany(TaskMilestone::class, 'assigned_to');
    }

    public function tasksAssignedFrom(): HasMany
    {
        return $this->hasMany(TaskMilestone::class, 'assigned_by');
    }

    public function latestTask(): HasOne
    {
        return $this->tasksAssignedTo()->one()->latestOfMany();
    }


    public function userteam(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'user_team', 'user_id', 'team_id');
    }

    public function latestuserteam()
    {
        return $this->belongsToMany(Team::class, 'user_team', 'user_id', 'team_id')
            ->withTimestamps()
            ->orderBy('id', 'desc')
            ->limit(1);
    }

    public function teamrequest(): HasMany
    {
        return $this->hasMany(TeamRequest::class);
    }

    public function userdetail(): HasOne
    {
        return $this->hasOne(UserDetail::class)->withDefault();
    }

    public function attendence(): HasMany
    {
        return $this->hasMany(Attendence::class);
    }

    public function shedules(): HasMany
    {
        return $this->hasMany(Shedule::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    public function task(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'creater_id');
    }

    public function actionplan(): HasMany
    {
        return $this->hasMany(Actionplan::class, 'submited_by');
    }

    public function courserequest(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_requests')->using(CourseRequest::class);
    }

    public function teamposition(): BelongsTo
    {
        return $this->belongsTo(TeamPositions::class, 'teamposition_id');
    }
}
