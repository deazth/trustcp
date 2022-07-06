<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class AreaBooking extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'area_bookings';
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

    public function getQRBtn($crud = false)
    {
      if($this->status == 'Active'){
        return '<a class="btn btn-sm btn-link" target="_blank" href="'
          . route('area.booking.qr', ['id' => $this->id ])
          . '" title="Get QR code."><i class="las la-qrcode"></i> QR</a>';
      }

      return '';
    }

    public function getAttendance($crud = false)
    {
      if($this->status == 'Active'){
        return '<a class="btn btn-sm btn-link" href="'
          . route('inv.event.info', ['id' => $this->id ])
          . '" title="View participant."><i class="las la-list-alt"></i> Info</a>';
      }

      return '';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function Meetingarea(){
      return $this->belongsTo(Seat::class, 'seat_id');
    }

    public function organizer(){
      return $this->belongsTo(User::class, 'user_id');
    }

    public function last_admin(){
      return $this->belongsTo(User::class, 'admin_id');
    }

    public function extra_equip(){
      return $this->belongsToMany(EquipmentType::class)->withPivot('count', 'status');
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
