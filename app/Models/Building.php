<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Building extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'buildings';
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

    public function SeatSummary(){

      $totalseat = 0;
      $usedseat = 0;
      $booked = 0;
      $vacant = 0;

      foreach($this->Floors as $fc){
        $data = $fc->SeatSummary();
        $totalseat += $data['total'];
        $booked += $data['booked'];
        $usedseat += $data['used'];
        $vacant += $data['vacant'];
      }

      return [
        'total' => $totalseat,
        'used' => $usedseat,
        'booked' => $booked,
        'vacant' => $vacant
      ];
    }

    public function GetAnak(){
      return $this->Floors;
    }

    public function GetLabel(){
      return $this->building_name;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function creator(){
      return $this->belongsto('App\Models\User','created_by');
    }
    public function updator(){
      return $this->belongsto('App\Models\User','updated_by');
    }

    public function Floors(){
      return $this->hasMany(Floor::class,'building_id')->where('status', 1);
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

    public function getLongLabelAttribute($value){
      return $this->GetLabel();
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    /* default value setters */
    protected static function boot()
    {
      parent::boot();
      Building::saving(function ($model) {
        $model->updated_by = backpack_user()->id;
      });

      Building::creating (function ($model) {
        $model->created_by = backpack_user()->id;
      });

      Building::deleting(function ($model) {
        $model->updated_by = backpack_user()->id;
      });
    }
}
