<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\CommonConfig;

class AppDownloadController extends Controller
{
  public function list(Request $req){

    // dd(\Auth::user());

    // check for existing file:
    //
    $ipa = \Storage::exists('reports/public/trust.ipa');
    // $plist = \Storage::exists('reports/public/trust.plist');
    $apk = \Storage::exists('reports/public/trust.apk');

    if($req->filled('alert')){
      return view('appdownload', ['alert' => $req->alert, 'ipa' => $ipa, 'apk' => $apk]);
    }

    return view('appdownload', ['ipa' => $ipa, 'apk' => $apk]);
  }

  public function upload(Request $req){
    if(!backpack_user()->hasPermissionTo('super-admin')){
      abort(403);
    }

    $type = $req->type;
    $storedfile = $req->file('inputfile')->storeAs('reports/public', 'trust.' . $type);

    if($req->type == 'ipa'){

      $bd_id = CommonConfig::updateOrCreate(['key' => 'ipa_bundle_id'], ['value' => $req->bundle_id]);
      $bd_ver = CommonConfig::updateOrCreate(['key' => 'ipa_bundle_version'], ['value' => $req->bundle_version]);

    }

    return redirect(route('app.list', ['alert' => $storedfile], false));

  }

  public function download(Request $req){
    return \Storage::download('reports/public/trust.' . $req->type, 'trust.' . $req->type);
  }

  public function getipa(){
    return \Storage::download('reports/public/trust.ipa', 'trust.ipa');
  }

  public function getplist(){
    // return \Storage::download('reports/public/trust.plist', 'trust.plist');
    $bd_id = CommonConfig::firstOrCreate(['key' => 'ipa_bundle_id'], ['value' => 'com.tm.trUSt']);
    $bd_ver = CommonConfig::firstOrCreate(['key' => 'ipa_bundle_version'], ['value' => '3.0.1']);

    return view('plist', [
      'bundle_version' => $bd_ver->value,
      'bundle_id' => $bd_id->value,
    ]);

  }

  public function delete(Request $req){
    if(!backpack_user()->hasPermissionTo('super-admin')){
      abort(403);
    }

    $msg = 'file deleted';
    if(\Storage::exists('reports/public/trust.' . $req->type)){
      \Storage::delete('reports/public/trust.' . $req->type);
    } else {
      $msg = 'reports/public/trust.' . $req->type . ' not exist';
    }

    return redirect(route('app.list', ['alert' => $msg], false));
  }
}
