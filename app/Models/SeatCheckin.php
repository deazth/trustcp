<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class SeatCheckin extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'seat_checkins';
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

    public function doCheckOut(){
      $now = new \Carbon\Carbon;
      $this->out_time = $now->toDateTimeString();
      $this->save();

      // then reduce the counter

      if($this->Seat){
        if($this->Seat->seat_utilized > 0){
          $this->Seat->decrement('seat_utilized');
        }

        if($this->Seat->seat_type == 'Seat'){
          // unbind the checkin from the reservation
          $usb = UserSeatBooking::where('seat_checkin_id', $this->id)->first();
          if($usb){
            $usb->seat_checkin_id = null;
            $usb->save();
          }
        }

      }

    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function Seat(){
      return $this->belongsTo(Seat::class);
    }

    public function EventAttendance(){
      return $this->belongsTo(EventAttendance::class);
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
