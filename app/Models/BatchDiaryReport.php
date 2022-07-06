<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class BatchDiaryReport extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'batch_diary_reports';
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

    public function dlExcel($crud = false)
    {
      if(isset($this->filename)){
        return '<a class="btn btn-sm btn-link" target="_blank" href="'
          . route('bdr.rpt.download', ['id' => $this->id ])
          . '" title="Download excel"><i class="las la-file-download"></i> Download</a>';
      }

      return '';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function User(){
      return $this->belongsTo(User::class, 'user_id');
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

      self::deleting(function ($model){
        if(\Storage::exists('reports/'.$model->filename)){
          \Storage::delete('reports/'.$model->filename);
        }
      });
    }
}
