<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\common\CommonHelper;

class SkillAdminController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:skill-admin']);
  }

  public function InvolveStats(Request $req){

    $this->data['title'] = 'Involvement Stats';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Involvement Stats' => false
    ];

    $catkontrol = new \App\Http\Controllers\Admin\Charts\InvolveStatChartController();
    $thechards[] = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => 'Involvement Survey Fill Rate'
    ];

    $this->data['thecharts'] = $thechards;
    return view('dash.involvestats', $this->data);

  }

  public function ListInvolveData(Request $req){
    $this->data['title'] = 'Involvement Stats';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Involvement Stats List' => false
    ];

    $gitdlabel = CommonHelper::GetCConfig('gitd_label', 'IT & Digital');

    $users = User::where('status', 1)->where('lob_descr', $gitdlabel)->get();
    $this->data['tmember'] = $users;

    return view('dash.involvelist', $this->data);
  }
}
