<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class FloorSection extends Model
{
    use \Venturecraft\Revisionable\RevisionableTrait;
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'floor_sections';
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

    public function SeatSummary($includebook = true){
      $totalseat = 0;
      $usedseat = 0;
      $booked = 0;
      $vacant = 0;

      foreach($this->FreeSeats as $seat){
        $data = $seat->SeatSummary($includebook);
        $totalseat += $data['total'];
        $booked += $data['booked'];
        $usedseat += $data['used'];
        $vacant += $data['vacant'];
      }

      return [
        'total' => $totalseat,
        'used' => $usedseat,
        'booked' => $booked,
        'vacant' => $vacant
      ];
    }

    public function GetAnak(){
      return $this->FreeSeats;
    }

    public function GetLabel(){
      return $this->label;
    }

    public function getLongLabelAttribute($value){
      $floor = $this->Floor;
      $office = $floor->Buildings;
      return $office->GetLabel() . ' - ' . $floor->GetLabel() . ' - ' . $this->GetLabel();
    }

    public function getAllQRBtn($crud = false)
    {
      // $param = 'activity_date={"from":"' . $this->record_date . '","to":"' . $this->record_date . '"}';
      // return '<a class="btn btn-sm btn-link" href="'
      //   . route('gwdactivity.index') . '?' . htmlspecialchars($param)
      //   . '" title="View Entries."><i class="las la-list-alt"></i> Info</a>';

      return '<a class="btn btn-sm btn-link" href="'
        . route('floorsection.getAllQr', ['fcid' => $this->id])
        . '" title="Get QR." target="_blank"><i class="las la-qrcode"></i> Get QRs</a>';

    }

    public function getAllSeats($crud = false)
    {
      return '<a class="btn btn-sm btn-link" href="'
        . route('seat.index', ['fcid' => $this->id])
        . '" title="Seats under this section"><i class="la la-chair"></i> Seats</a>';

    }

    public function viewLayoutBtn($crud = false)
    {
      if($this->layout_file){
        return '<a class="btn btn-sm btn-link" target="_blank" href="'
          . route('inventory.fc.getlayout', ['id' => $this->id ])
          . '" title="View Layout"><i class="las la-eye"></i> Layout</a>';
      }

      return '';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function Floor(){
      return $this->belongsTo(Floor::class);
    }

    public function Seats(){
      return $this->hasMany(Seat::class, 'floor_section_id')->where('seat_type', 'Seat')->where('status', 1);
    }

    public function FreeSeats(){
      return $this->hasMany(Seat::class, 'floor_section_id')
      ->where('seat_type', 'Seat')
      ->where('free_seat', true)
      ->where('status', 1);
    }

    public function BookableSeats(){
      return $this->hasMany(Seat::class, 'floor_section_id')
      ->where('seat_type', 'Seat')
      ->where('allow_booking', true)
      ->where('status', 1);
    }

    public function MeetingAreas(){
      return $this->hasMany(Seat::class, 'floor_section_id')->where('seat_type', 'Meeting Area')->where('status', 1);
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

    public function setLayoutFileAttribute($value){

      $attribute_name = "layout_file";
      $disk = "local";
      $destination_path = "reports/layout";

      $this->uploadFileToDisk($value, $attribute_name, $disk, $destination_path);
    }

    protected static function boot()
    {
      parent::boot();

      self::deleting(function ($model){
        if(\Storage::exists($model->layout_file)){
          \Storage::delete($model->layout_file);
        }
      });
    }
}
