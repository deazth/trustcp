<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UpdatedByTrait;

class PersonalSkillset extends Model
{
  use CrudTrait;

  const level_desc = ['1' => 'Beginner', '2' => 'Intermediate', '3' => 'Expert'];
  //use UpdatedByTrait;

  /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

  // protected $table = 'user_seat_bookings';
  // protected $primaryKey = 'id';
  // public $timestamps = false;
  protected $guarded = ['id'];
  // protected $fillable = [];
  // protected $hidden = [];
  // protected $dates = [];



  /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
  public function CommonSkillset()
  {
    return $this->belongsTo(CommonSkillset::class, 'common_skill_id');
  }

  public function User()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function SkillType()
  {


    return $this->belongsTo(SkillType::class);
  }

  public function SkillCategory()
  {
    return $this->belongsTo(SkillCategory::class);
  }

  public function openGoogle($crud = false)
  {
    return '<a class="btn btn-xs btn-default" target="_blank" href="http://google.com?q=' . urlencode($this->text) . '" data-toggle="tooltip" title="Just a demo custom button."><i class="fa fa-search"></i> Google it</a>';
  }
  public function active_status()
  {
    $stat = 1;
    $n = $this->status;

    switch ($n) {
      case 'D':
        $stat = 0;
        break;
      case 'A':
        $stat = 1;
        break;
      case 'Y':
        $stat = 1;
        break;

      default:
        $stat = 1;
        
    }
    return $stat;
  }

  public function active_status2(){
    if($this->active_status() == 1)
    {return 'Active';}
    else
    {return 'Deleted';}
  }

  public function level_desc_arr(){
    return PersonalSkillset::level_desc;
  }


}
