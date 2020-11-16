<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
  protected $table = 'user_profiles';

  // protected $fillable = ['bio', 'twitter', 'profession_id'];

  protected $guarded = [];

  public function profession()
  {
    return $this->belongsTo(Profession::class);
  }
}