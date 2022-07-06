<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Models\Traits\CreatedByTrait;
use App\Models\Traits\UpdatedByTrait;
//use Illuminate\Database\Eloquent\Factories\HasFactory;


class CommonSkillset extends Model
{
    use CrudTrait;
    use UpdatedByTrait; // <---- for both created and updated by
    //use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    //use HasFactory;

    protected $table = 'common_skillsets';
    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function SkillCategory(){
        return $this->belongsTo(SkillCategory::class);
      }

      public function SkillType(){
        return $this->belongsTo(SkillType::class,'skill_type_id');
      }

}


