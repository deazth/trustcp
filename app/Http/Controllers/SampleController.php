<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SampleController extends Controller
{
    //
    public function mun(Request $req){
        return view('sample.mun',[]);

    }
    public function nana(Request $req){
        return view('sample.nana',[]);

    }

    public function amer(Request $req){
        return view('sample.amer',[]);
        
    }
}
