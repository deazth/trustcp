<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class DailyPerformance extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'daily_performances';
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

    public function recalcHours(){
      $this->actual_hours = GwdActivity::where('daily_performance_id', $this->id)->sum('hours_spent');
      $this->save();
    }

    public function getCutiInfo(){
      if($this->is_public_holiday){
        return $this->PublicHoliday->name;
      } elseif($this->is_off_day){
        return $this->LeaveType->descr;
      } else {
        return '';
      }
    }

    public function addHours($hours){
      $this->actual_hours +=  $hours;
      $this->save();
    }

    public function getEntriesBtn($crud = false)
    {
      // $param = 'activity_date={"from":"' . $this->record_date . '","to":"' . $this->record_date . '"}';
      // return '<a class="btn btn-sm btn-link" href="'
      //   . route('gwdactivity.index') . '?' . htmlspecialchars($param)
      //   . '" title="View Entries."><i class="las la-list-alt"></i> Info</a>';

      return '<a class="btn btn-sm btn-link" href="'
        . route('gwdactivity.index', ['act_date' => $this->record_date, 'uid' => $this->user_id])
        . '" title="View Entries."><i class="las la-list-alt"></i> Diary Entries</a>';

    }

    public function getResetdfBtn($crud = false)
    {
      return '<a class="btn btn-sm btn-link" href="'
        . route('dailyperformance.resetdf', ['dfid' => $this->id])
        . '" title="reset."><i class="las la-sync"></i> Reset Expected</a>';

    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function Activities(){
      return $this->hasMany(GwdActivity::class, 'daily_performance_id');
    }

    public function LeaveType(){
      return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function Division(){
      return $this->belongsTo(Unit::class, 'unit_id', 'pporgunit');
    }

    public function User(){
      return $this->belongsTo(User::class, 'user_id');
    }

    public function PublicHoliday(){
      return $this->belongsTo(PublicHoliday::class);
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

    public function getInfoAttribute(){
      if($this->is_off_day){
        return $this->LeaveType->descr;
      }

      if($this->is_public_holiday){
        return $this->PublicHoliday->name;
      }

      return '';
    }

    /* some sort of performance calculation */
    public function getPerformanceAttribute(){
      $exph = $this->expected_hours;
      $retval = 0;
      if($this->expected_hours == 0){
        $exph = 8;
        $extra = $this->actual_hours / $exph * 100;
        $retval = 100 + $extra;
      } else {
        if($this->actual_hours > $this->expected_hours){
          $rem = $this->actual_hours - $this->expected_hours;
          $retval = $rem / $this->expected_hours * 100 + 100;
        } else {
          $retval = $this->actual_hours / $this->expected_hours * 100;
        }

      }

      return intval($retval);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function __toString(){
      $data = [
        'id' => $this->id,
        'created_at' => $this->created_at,
        'user_id' => $this->user_id,
        'record_date' => $this->record_date,
        'expected_hours' => $this->expected_hours,
        'is_public_holiday' => $this->is_public_holiday,
        'public_holiday_id' => $this->public_holiday_id,
        'remark' => $this->remark
      ];

      return json_encode($data);
    }
}
