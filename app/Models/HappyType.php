<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HappyType extends Model
{
    public $incrementing = false;

    public function ImgLink(){
        return '/images/smile/'.$this->remark.'.svg';
      }
}

