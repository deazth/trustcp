<?php

namespace App\common;

use App\Models\User;
use App\Models\SkillCategory;
use App\Models\SkillType;
use App\Models\CommonSkillset;

class SkillHelper
{
  public static function SSGetCat(){
      $sc = SkillCategory::all();

      return $sc;

    
  }


  public static function GetSkillTypeByCat($cat){
  
    $skill_types = SkillType::where('skill_category_id',$cat);
    return $skill_types;
 
}

public static function GetCommonSkillByType($skill_type){
  
  $skill_types = CommonSkillset::where('skill_type_id',$skill_type);
  return $skill_types;


}

}
