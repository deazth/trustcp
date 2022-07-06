<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class UserTeamHistory extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'user_team_histories';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    // public function getOldBossAttribute(){
    //   if($this->old_superior_id && $this->old_superior_id != 0){
    //     return $this->oldboss()->name;
    //   } else {
    //     return 'N/A';
    //   }
    // }
    //
    // public function getNewBossAttribute(){
    //   if($this->new_superior_id != 0){
    //     return $this->newboss->name;
    //   } else {
    //     return 'N/A';
    //   }
    // }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function oldboss(){
      return $this->belongsTo(User::class, 'old_superior_id');
    }

    public function user(){
      return $this->belongsTo(User::class, 'user_id');
    }

    public function newboss(){
      return $this->belongsTo(User::class, 'new_superior_id');
    }

    public function editor(){
      return $this->belongsTo(User::class, 'edited_by');
    }



    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
