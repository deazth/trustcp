<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;
use App\common\GDWActions;

class StaffLeave extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'staff_leaves';
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

    public function createCuti(){
      $sdate = new Carbon($this->start_date);
      $edate = new Carbon($this->end_date);
      $edate->addDay();

      while($edate->greaterThan($sdate)){
        $oned = GDWActions::GetDailyPerfObj($this->user_id, $sdate);
        if($oned->zerorized == true){
          // do nothing if zerorized
        } else {
          if($oned->expected_hours > 0){
            // only set as leave if default expected is not 0
            $oned->is_off_day = true;
            $oned->leave_type_id = $this->leave_type_id;

            if($this->LeaveType->hours_value != 8){
              $oned->expected_hours = $this->LeaveType->hours_value;
            }

            $oned->save();
          }
        }


        $sdate->addDay();
      }
    }

    public function reverseCuti(){
      // GDWActions::GetExpectedHours($date)
      $sdate = new Carbon($this->start_date);
      $edate = new Carbon($this->end_date);
      $edate->addDay();

      $user = User::find($this->user_id);
      // $friday = $user->Division->friday_hours;

      while($edate->greaterThan($sdate)){

        $oned = GDWActions::GetDailyPerfObj($this->user_id, $sdate);
        if($oned){
          if($oned->zerorized == true){
            // dont do anything if this day is zerorized
          } else {
            $oned->is_off_day = false;
            $oned->leave_type_id = null;
            // $oned->expected_hours = GDWActions::GetExpectedHours($sdate, $oned, null, $friday);
            $oned->save();

            // reset the expected hour
            $twod = GDWActions::GetDailyPerfObj($this->user_id, $sdate, false, true);
          }
        }

        $sdate->addDay();
      }
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function LeaveType(){
      return $this->belongsTo(LeaveType::class);
    }

    public function User(){
      return $this->belongsTo(User::class);
    }

    public function Creator(){
      return $this->belongsTo(User::class, 'created_by');
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

    // protected static function boot()
    // {
    //   parent::boot();
    //   self::creating (function ($model) {
    //     $model->created_by = backpack_user()->id;
    //   });
    // }
}
