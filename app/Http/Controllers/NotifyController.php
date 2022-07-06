<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotifyController extends Controller
{
  public function MarkAllAsRead(){
    backpack_user()->unreadNotifications->markAsRead();
    return redirect()->back();
  }

  public function ReadNotify($id){
    $notobj = backpack_user()->unreadNotifications->find($id);
    if($notobj){
      $notobj->markAsRead();
      return redirect()->route($notobj->data['route_name'], $notobj->data['param']);
    } else {
      \Alert::error('Notification ID no longer valid')->flash();
      return redirect()->route('backpack.dashboard');
    }
  }
}
