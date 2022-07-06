<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\SeatBookExpired;

class UserSeatBooking extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'user_seat_bookings';
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
    public function CancelBooking($status, $remark){
      $this->status = $status;
      $this->admin_remark = $remark;
      $this->save();
      // if($this->Seat){
      //   // find the related SeatBooking
      //   $bdate = new \Carbon\Carbon($this->start_time);
      //   if(intval($this->time_slots) == 3){
      //     $sb1 = $this->Seat->SeatBooking($bdate->toDateString(), 1);
      //     $sb2 = $this->Seat->SeatBooking($bdate->toDateString(), 2);
      //     $sb1->decrement('booked_count');
      //     $sb2->decrement('booked_count');
      //   } else {
      //     $sb = $this->Seat->SeatBooking($bdate->toDateString(), intval($this->time_slots));
      //     $sb->decrement('booked_count');
      //   }
      // }

      $this->User->notify(new SeatBookExpired($this));
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function Seat(){
      return $this->belongsTo(Seat::class, 'seat_id');
    }

    public function FloorSection(){
      return $this->belongsTo(FloorSection::class, 'floor_section_id');
    }

    public function User(){
      return $this->belongsTo(User::class, 'user_id');
    }

    public function Section(){
      return $this->hasOneThrough(FloorSection::class, Seat::class);
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
