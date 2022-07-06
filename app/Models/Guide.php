<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'guides';
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

    public function dlGuideFile($crud = false)
    {
      if($this->url){
        return '<a class="btn btn-sm btn-link" target="_blank" href="'
          . route('uguide.download', ['id' => $this->id ])
          . '" title="Download guide"><i class="las la-file-download"></i> Download</a>';
      }

      return '';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

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

    public function setUrlAttribute($value){

      $attribute_name = "url";
      $disk = "local";
      $destination_path = "reports/guides";

      $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);
    }


    /* default value setters */
    protected static function boot()
    {
      parent::boot();

      self::creating (function ($model) {
        $model->added_by = backpack_user()->id;
      });

      self::deleting(function ($model){
        if(\Storage::exists($model->url)){
          \Storage::delete($model->url);
        }
      });
    }
}
