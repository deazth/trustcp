<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelLike\Traits\Likeable;

class Idea extends Model
{
    use CrudTrait;
    use SoftDeletes;
    use Likeable;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'ideas';
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

    public function getLikeNett(){
      return $this->likers()->count();
    }

    public function getLikeBtn($crud = false)
    {
      $btncol = $this->isLikedBy(backpack_user()) ? ' text-success ' : '';
      return '<a class="btn btn-link' . $btncol . '" href="' . route('ideabox.togglelike', ['id' => $this->id ]). '" title="Like"><i class="las la-thumbs-up"></i></a>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function User(){
      return $this->belongsTo(User::class);
    }

    public function Category(){
      return $this->belongsTo(IdeaCategory::class, 'idea_category_id');
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

    public function getLikesAttribute($value){
      return $this->likers()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */


    protected static function boot()
    {
      parent::boot();

      self::creating (function ($model) {
        $model->user_id = backpack_user()->id;
      });

    }
}
