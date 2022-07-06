<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyPerformance;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\News;
use Backpack\CRUD\app\Library\Widget;
use App\common\CommonHelper;
use App\common\UserHelper;
use \Carbon\Carbon;

class NewsController extends Controller
{



  public function overview(Request $req){
    $this->data['title'] = 'News';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'News' => false
    ];

    $userid = $req->user_id ?? backpack_user()->id;
    $user = User::findOrFail($userid);
    $this->data['user'] = $user;

    if(auth()->check()){
        UserHelper::UpdateUnreadNews(auth()->user()->id);
      }

      $ui = UserInfo::where('user_id',$userid)->first();
      //dd($ui);
      
      //$newlist = News::where("created_at",">=",$ui->last_news_read)->orderBy('created_at', 'DESC')->limit(20)->get();
      $newlist = News::orderBy('created_at', 'DESC')->limit(20)->get();

      $this->data['news'] =  $newlist;


       $this->data['byID'] =  false;
    return view('staff.news.overview', $this->data);
  }

  public function newsById($newsId){
    $this->data['title'] = 'News';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'News' => false
    ];

    $userid = $req->user_id ?? backpack_user()->id;
    $user = User::findOrFail($userid);
    $this->data['user'] = $user;

    $news = News::where('id',$newsId)->get();
    $this->data['news'] =  $news;
    $this->data['byID'] =  true;

    return view('staff.news.overview', $this->data);
  }


  public function carousel(){

    $this->data['title'] = 'News';
    
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'News' => false
    ];
     
    $newlist = News::orderBy('created_at', 'DESC')->limit(10)->get();

    $this->data['news'] =  $newlist;
    $this->data['byID'] =  true;
    return view('staff.news.carousel',  $this->data);
  }
}
