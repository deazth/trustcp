<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Involvement extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'involvements';
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

    public function GetSumPerc(){
      $sum = 0;
      foreach ($this->Jobscopes as $key => $value) {
        $sum += $value->pivot->perc;
      }
      return $sum;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function Jobscopes(){
      return $this->belongsToMany(Jobscope::class)->withPivot('perc');;
    }

    public function BauExp(){
      return $this->belongsTo(BauExperience::class, 'bau_experience_id');
    }

    public function BauExpType(){
      return $this->belongsTo(BauExpType::class, 'bau_exp_type_id');
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

    protected static function boot()
    {
      parent::boot();
      self::creating (function ($model) {
        $model->added_by = backpack_user()->id;
      });
    }
}
