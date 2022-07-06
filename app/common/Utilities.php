<?php

namespace App\common;

use App\Models\CommonConfig;

class Utilities
{
  // get a common config value, or create new if it does not exist
  public static function GetCommonConfig($key, $default_value){

    // first, try to find for existing key
    $curr = CommonConfig::where('key', $key)->first();
    if($curr){
      return $curr->value;
    }

    // key not exist. create new with the given default value
    $curr = new CommonConfig;
    $curr->key = $key;
    $curr->value = $default_value;
    $curr->save();

    return $default_value;
  }
}
