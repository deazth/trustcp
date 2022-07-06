<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guide;

class UserGuideController extends Controller
{
  public function downloadGuide(Request $req){
    if($req->filled('id')){
      $guide = Guide::find($req->id);
      if($guide){
        if (!\Storage::exists($guide->url)) {
          return response()->file(public_path('img/notavailable.png'));
        }

        $file = \Storage::get($guide->url);
        $type = \Storage::mimeType($guide->url);
        $response = \Response::make($file, 200)->header("Content-Type", $type);

        return $response;
      } else {
        \Alert::error('selected guide not found')->flash();
        return redirect()->back();
      }
    } else {
      return redirect()->route('uguide.index');
    }
  }

  public function index(){
    $recs = Guide::orderBy('title', 'ASC')->get();

    return view('userguide', [
      'guides' => $recs
    ]);
  }
}
