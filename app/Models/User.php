<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
  use Notifiable, SoftDeletes;
  
  /* protected $fillable = [
    'name', 'email', 'password'
  ]; */

  protected $guarded = [];        // Video 2-18

  /* public function getPerPage()
  {
      return parent::getPerPage() * 2;  // Video 2-21
  } */

  public function profile()
  {
    return $this->hasOne(UserProfile::class)->withDefault();
  }

  public function skills()
  {
    return$this->belongsToMany(Skill::class, 'user_skill');
  }

  public function team()
  {
    return$this->belongsTo(Team::class)->withDefault();
  }

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password', 'remember_token',
  ];

  public static function findByEmail($email)
  {
    return static::where(compact('email'))->first();
  }

  public function isAdmin()
  {
    // return $this->email === 'superadmin@admin.net';
    return $this->role === 'admin';
  }

  public function scopeSearch($query, $search)
  {
    // Si la búsqueda esta vacía no se ejecute el método
    if (empty ($search)) {
      return;
    }

    $query->where('name', 'like', "%{$search}%")
      ->orWhere('email', 'like', "%{$search}%")
      ->orWhereHas('team', function ($query) use ($search) {
          $query->where('name', 'like', "%{$search}%");
      });
  }

  protected $casts = [
    'active' => 'bool',
  ];

  public function scopeByState($query, $state)
  {
    if ($state == 'active') {
      return $query->where('active', true);
    }

    if ($state == 'inactive') {
      return $query->where('active', false);
    }
  }

  /**
   * Buscar si el rol dado es admin o user
   */
  public function scopeByRole($query, $role)
  {
    if (in_array($role, ['user', 'admin'])) {
        $query->where('role', $role);
    }
  }

  public function setStateAttribute($value)
  {
    $this->attributes['active'] = $value == 'active';
  }

  public function getStateAttribute()
  {
    if ($this->active !== null) {
      return $this->active ? 'active' : 'inactive';
    }
  }

  /* public function getNameAttribute()
  {
    return "{$this->first_name} {$this->last_name}";
  } */
  
  // Crear con transaction, que los datos no se persistan en la bd
  /* public static function createUser($data)
  {
    DB::transaction(function() use ($data) {
      $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => bcrypt($data['password']),
      ]);
      $user->profile()->create([
        'bio'     => $data['bio'],
        'twitter' => $data['twitter'],
      ]);
    });
  } */
}