<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Session;

class AdminController extends Controller
{
    protected $data = []; // the information we send to the view

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(backpack_middleware());
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard_ori()
    {
        $this->data['title'] = trans('backpack::base.dashboard'); // set the page title
        // get happy data from session
        // if 0 happy page shown
        $happy = session('happy', 0);
        $show_happy = 0;
        if($happy==0){
            $show_happy = 1;
            session(['happy'=>1]);
        }

        //dd(session('happy'));


        $user = backpack_user();
        $this->data['breadcrumbs'] = [
            'Home' => false,
        ];
        $this->data['user'] = $user;
        $this->data['show_happy'] = $show_happy;

      //  dd($this->data);



        return view(backpack_view('dashboard'), $this->data);
    }

    public function dashboard()
    {
      $this->data['title'] = 'User Home'; // set the page title
      return view('userhome', $this->data);
    }

    /**
     * Redirect to the dashboard.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        // The '/admin' route is not to be used as a page, because it breaks the menu's active state.
        return redirect(backpack_url('dashboard'));
    }
}
