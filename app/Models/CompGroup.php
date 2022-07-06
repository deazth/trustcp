<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompGroup extends Model
{
    use CrudTrait;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'comp_groups';
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

    public function showActTypeList($crud = false)
    {
      return '<a class="btn btn-sm btn-link" href="'
        . route('ctacttype.index', ['gid' => $this->id ])
        . '" title="Activity Types"><i class="las la-list-alt"></i> Activity Types</a>';

    }

    public function showUserList($crud = false)
    {
      return '<a class="btn btn-sm btn-link" href="'
        . route('ct-user-manage.index', ['gid' => $this->id ])
        . '" title="Activity Types"><i class="las la-user-friends"></i> Staff List</a>';

    }

    public function showBatchRpt($crud = false)
    {
      return '<a class="btn btn-sm btn-link" href="'
        . route('batch-diary-report.index', ['gid' => $this->id ])
        . '" title="Diary Report"><i class="lar la-file-excel"></i> Diary Rpt</a>';

    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function Units(){
      return $this->hasMany(Unit::class, 'comp_group_id');
    }

    public function Caretakers(){
      return $this->belongsToMany(User::class);
    }

    public function Lovgps(){
      return $this->belongsToMany(Lovgp::class);
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


    /* default value setters */
    protected static function boot()
    {
      parent::boot();


      CompGroup::creating (function ($model) {
        $model->created_by = backpack_user()->id;
      });

      CompGroup::deleting(function ($model) {
        $model->deleted_by = backpack_user()->id;
      });
    }
}
