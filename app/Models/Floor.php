<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Floor extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'floors';
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

      foreach($this->FloorSections as $fc){
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

    public function GetLabel(){
      return $this->floor_name;
    }

    public function GetAnak(){
      return $this->FloorSections;
    }

    public function getLongLabelAttribute($value){
      $office = $this->Buildings;
      return $office->GetLabel() . ' - ' . $this->GetLabel();
    }

    public function viewLayoutBtn($crud = false)
    {
      if($this->layout_file){
        return '<a class="btn btn-sm btn-link" target="_blank" href="'
          . route('inventory.floor.getlayout', ['id' => $this->id ])
          . '" title="View Layout"><i class="las la-eye"></i> Layout</a>';
      }

      return '';
    }

    public function getAllSections($crud = false)
    {
      return '<a class="btn btn-sm btn-link" href="'
        . route('floorsection.index', ['fid' => $this->id])
        . '" title="Seats under this section"><i class="la la-chair"></i> Sections</a>';

    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function Buildings(){
      return $this->belongsTo(Building::class, 'building_id');
    }
    public function Createdby(){
      return $this->belongsTo(User::class,'created_by','id');
    }

    public function FloorSections(){
      return $this->hasMany(FloorSection::class, 'floor_id')->where('status', 1);
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

    // public function getLayoutFileAttribute($value){
    //   return \Storage::url($value);
    // }

    public function setLayoutFileAttribute($value){

      $attribute_name = "layout_file";
      $disk = "local";
      $destination_path = "reports/layout";

      $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);
    }


    /* default value setters */
    protected static function boot()
    {
      parent::boot();
      Floor::saving(function ($model) {
        $model->created_by = backpack_user()->id;
      });

      Floor::deleting(function ($model){
        if(\Storage::exists($model->layout_file)){
          \Storage::delete($model->layout_file);
        }
      });
    }
}
