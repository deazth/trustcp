<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class AppreciateCard extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'appreciate_cards';
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

    public function viewCard($crud = false)
    {
      return '<a class="btn btn-sm btn-link" href="'
        . route('appreciatecard.preview', ['cid' => $this->id])
        . '" title="View card." target="_blank"><i class="las la-eye"></i> View</a>';

    }

    public function type(){
    $retval = "Default";
    switch ($this->template) {
      case 'awesome':
        $retval = 'Awesome';
        break;
      case 'gj':
        $retval = 'Good Job';
        break;
      case 'superb':
        $retval = 'Superb';
        break;
      case 'welldone':
        $retval = 'Well Done';
        break;
      default:
        break;
    }

    return $retval;
  }

  public function namecolor(){
    $retval = "Default";
    switch ($this->template) {
      case 'awesome':
        $retval = '#155';
        break;
      case 'gj':
        $retval = '#171';
        break;
      case 'superb':
        $retval = '#711';
        break;
      case 'welldone':
        $retval = '#717';
        break;
      default:
        break;
    }

    return $retval;
  }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function recipient(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender(){
        return $this->belongsTo(User::class, 'sender_id');
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
