<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Administrator
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'admin_users';

    //company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $name = '';
            $a = '';
            $b = '';
            if($model->first_name != null && strlen($model->first_name) > 0){
                $a = $model->first_name;
            }

            if($model->last_name != null && strlen($model->last_name) > 0){
                $b = $model->last_name;
            }

            $name = $a.' '.$b;
            
            if($name != null && strlen($name) > 0){
                $model->name = $name;
            }
            $model->username = $model->email;
            return $model;
        });

        static::updating(function ($model) {
            $name = '';
            $a = '';
            $b = '';

            if($model->first_name != null && strlen($model->first_name) > 0){
                $a = $model->first_name;
            }

            if($model->last_name != null && strlen($model->last_name) > 0){
                $b = $model->last_name;
            }

            $name = $a.' '.$b;
            //$name = trim($name);
            
            if($name != null && strlen($name) > 0){
                $model->name = $name;
            }
            $model->username = $model->email;
            return $model;
        });        
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
