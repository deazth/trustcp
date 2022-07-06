<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Laravel\Passport\HasApiTokens;
use Overtrue\LaravelLike\Traits\Liker;
use App\common\CommonHelper;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, CrudTrait, HasApiTokens;
    use Liker;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'staff_no',
        'report_to',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function current_checkins(){
      return $this->belongsToMany(SeatCheckin::class);
    }

    public function getIdNameAttribute($value){
      return $this->staff_no . ' - ' . $this->name;
    }

    public function getSpNameAttribute($value){
      $thesp = $this->Boss;
      if($thesp){
        return $thesp->id_name;
      } else {
        return 'N/A';
      }
    }

    public function CompGroups(){
      return $this->belongsToMany(CompGroup::class);
    }

    public function Unit(){
      return $this->belongsTo(Unit::class);
    }


    public function Division()
    {
      return $this->belongsTo('App\Models\Unit', 'unit_id');

        // if (isset($this->unit_id)) {
        //     return $this->belongsTo('App\Models\Unit', 'unit_id');
        // } else {
        //     $porg = 0;
        //     if ($this->isvendor == 1) {
        //       $porg = 123;
        //     }
        //
        //     $un = Unit::where('pporgunit', $porg)->first();
        //     // update own unit id
        //     $this->unit_id = $un->id;
        //     $this->unit = $un->pporgunitdesc;
        //     $this->lob = $porg;
        //     $this->save();
        //
        //     // then return the relationship
        //     return $this->belongsTo('App\Models\Unit', 'unit_id');
        // }
    }

    public function Boss()
    {
      // if(!isset($this->report_to) || $this->report_to == 0){
      //   return null;
      // }

      return $this->hasOne(User::class, 'persno', 'report_to');
    }

    public function Subordinates()
    {
      return $this->hasMany(User::class, 'report_to', 'persno')->where('status', 1);
    }

    public function getPersonalSkillset()
    {
      $cs= new CommonSkillset;
        return $this->hasMany(PersonalSkillset::class, 'user_id')
        ->join($cs->getTable() .' as cs','common_skill_id','cs.id')
        ->get(['cs.id as skill_id','name','level']);


     }

     public function getPersonalSkillset2()
    {
      $cs= new CommonSkillset;
      return
      $this->hasMany(PersonalSkillset::class, 'user_id')
      ->where('status','!=','D')
      ->join($cs->getTable() .' as cs','common_skill_id','cs.id')
      ->get(['cs.id as skill_id','name','level']);

     }

    public function getMyImageUrl(){
      return route('staff.image', ['staff_no' => $this->staff_no]);
    }

    public function leaveFromSAP($crud = false)
    {
      return '<a class="btn btn-sm btn-link" target="_blank" href="'
        . route('leave-information.index', ['uid' => $this->id ])
        . '" title="SAP Leave"><i class="las la-leaf"></i> SAP Leave</a>';

    }

    public function loadedLeave($crud = false)
    {
      return '<a class="btn btn-sm btn-link" target="_blank" href="'
        . route('staffleave.index', ['uid' => $this->id ])
        . '" title="Leave in trUSt"><i class="lab la-envira"></i> Leave in trUSt</a>';

    }

    public function isAdmin(){
      return $this->hasPermissionTo('diary-admin') || $this->hasPermissionTo('super-admin');
    }

    public function isGITD(){
      return $this->lob_descr == CommonHelper::GetCConfig('gitd_label', 'IT & Digital');
    }

    public function Involvements(){
      return $this->hasMany(Involvement::class);
    }

    public function GetUserInfo(){
      return \App\common\UserHelper::GetUserInfo($this->id);
    }

    public function InvTotalPerc(){
      $inv = $this->Involvements;
      if($inv){
        return $inv->sum('perc');
      }

      return 0;
    }
}
