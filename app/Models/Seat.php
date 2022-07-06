<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Seat extends Model
{
    use CrudTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;

    protected $table = 'seats';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    public function floor_section(){
      return $this->belongsTo(FloorSection::class);
    }

    public function Floor(){
      return $this->belongsTo(Floor::class);
    }

    public function Building(){
      return $this->belongsTo(Building::class);
    }

    public function SeatSummary($includebook = true){
      $noon = new \Carbon\Carbon;
      $noon->hour = 13;
      $noon->minute = 0;
      $noon->second = 0;
      $now = new \Carbon\Carbon;
      $timeslot = $now->lt($noon) ? 1 : 2;
      if($includebook == true){
        $sb = $this->SeatBooking($now->toDateString(), $timeslot);
        $booked = $sb->booked_count;
      } else {
        $booked = 0;
      }


      $totalseat = $this->seat_capacity;
      $usedseat = $this->seat_utilized;
      $vacant = $totalseat - $usedseat - $booked;

      return [
        'total' => $totalseat,
        'used' => $usedseat,
        'booked' => $booked,
        'vacant' => $vacant
      ];
    }

    public function SeatBooking($date, $time_slot){
      $sb = \App\Models\SeatBooking::firstOrCreate([
          'seat_id' => $this->id,
          'time_slot' => $time_slot,
          'book_date' => $date
        ], ['booked_count' => 0]
      );

      return $sb;
    }

    public function GetLabel(){
      return $this->label;
    }

    public function getSeatUrlAttribute($value){
      return route('inv.seat.docheckin', ['qr' => $this->qr_code] );
    }

    public function getLongLabelAttribute($value){
      $fc = $this->floor_section;
      $floor = $fc->Floor;
      $office = $floor->Buildings;
      return $office->GetLabel() . ' - ' . $floor->GetLabel() . ' - ' . $fc->GetLabel() . ' - ' . $this->GetLabel();
    }

    public function getParentLongLabelAttribute($value){
      $fc = $this->floor_section;
      $floor = $fc->Floor;
      $office = $floor->Buildings;
      return $office->GetLabel() . ' - ' . $floor->GetLabel() . ' - ' . $fc->GetLabel();
    }

    public function getQRBtn($crud = false)
    {
      return '<a class="btn btn-sm btn-link" target="_blank" href="' . route('inv.seat.qr', ['id' => $this->id ]). '" title="Get QR code."><i class="las la-qrcode"></i> QR</a>';
    }

    public function EquipmentTypes(){
      return $this->belongsToMany(EquipmentType::class);
    }




    /* default value setters */
    protected static function boot()
    {
      parent::boot();
      Seat::saving(function ($model) {
        if(backpack_user()){
        $model->updated_by = backpack_user()->id;
        }
      });

      Seat::creating (function ($model) {
        $model->created_by = backpack_user()->id;
        $model->qr_code = \Illuminate\Support\Str::uuid()->toString();

      });

      Seat::deleting(function ($model) {
        $model->updated_by = backpack_user()->id;
      });
    }

}
