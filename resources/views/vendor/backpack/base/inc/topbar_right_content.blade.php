<!-- This file is used to store topbar (right) items -->

<?php
  $announce = \App\common\CommonHelper::GetAnnouncements();
  $notifies = backpack_user()->unreadNotifications;
?>

@can('alert-infra')
  <?php
    $pbook = \App\common\CommonHelper::SBGetPendingBookingCount();
    $tbook = \App\common\CommonHelper::SBGetTodayBookingReqCount();
    $totalsb = $pbook + $tbook;
  ?>

  <li class="nav-item dropdown pr-4">
    <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
      <i class="las la-building text-light" title="SB Alert"><span class="badge badge-info">{{ $totalsb }}</span></i>
    </a>
    <div class="dropdown-menu {{ config('backpack.base.html_direction') == 'rtl' ? 'dropdown-menu-left' : 'dropdown-menu-right' }} mr-4 pb-1 pt-1">
      <a class="dropdown-item" href="{{ route('areabooking.index', ['pending' => 'true']) }}">Pending SB Approval: {{ $pbook }}</a>
      <a class="dropdown-item" href="{{ route('areabooking.index', ['extrareq' => 'true', 'start_today' => 'true']) }}">Upcoming booking with request: {{ $tbook }}</a>
    </div>
  </li>
@endcan

@if(sizeof($announce) > 0)
<li class="nav-item dropdown pr-4">
  <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
    <i class="las la-bullhorn text-light" title="Announcements"><span class="badge badge-danger">{{ sizeof($announce) }}</span></i>
  </a>
  <div class="dropdown-menu {{ config('backpack.base.html_direction') == 'rtl' ? 'dropdown-menu-left' : 'dropdown-menu-right' }} mr-4 pb-1 pt-1">
    <a class="dropdown-item" href="#"><b>Announcements</b></a>
    @foreach($announce as $anan)
      @if($anan->url == '')
        <a class="dropdown-item" href="#" >{{ $anan->content }}</a>
      @else
        <a class="dropdown-item" href="{{ $anan->url }}" target="_blank">{{ $anan->content }}</a>
      @endif

    @endforeach
  </div>
</li>
@endif

<li class="nav-item dropdown pr-4">
  <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
    <i class="la la-bell text-light" title="Notification"></i>
    @if($notifies->count() > 0)
    <span class="badge badge-light">{{ $notifies->count() }}</span>
    @endif
  </a>
  <div class="dropdown-menu {{ config('backpack.base.html_direction') == 'rtl' ? 'dropdown-menu-left' : 'dropdown-menu-right' }} mr-4 pb-1 pt-1">
    @if($notifies->count() == 0)
    <div class="dropdown-item">No unread notifications</div>
    @else
    <a class="dropdown-item" href="{{ route('notify.clear') }}"><i class="la la-broom text-primary"></i> Mark all as read</a>
    <div class="dropdown-divider"></div>
    @foreach($notifies as $noti)
    <a class="dropdown-item" href="{{ route('notify.read', ['id' => $noti->id])}}"><i class="{{ $noti->data['icon'] }} text-primary"></i> {{ $noti->data['text'] }}</a>
    @endforeach
    @endif
  </div>
</li>


{{-- <li class="nav-item d-md-down-none"><a class="nav-link" href="#"><i class="la la-bell"></i><span class="badge badge-pill badge-danger">5</span></a></li>
<li class="nav-item d-md-down-none"><a class="nav-link" href="#"><i class="la la-list"></i></a></li>
<li class="nav-item d-md-down-none"><a class="nav-link" href="#"><i class="la la-map"></i></a></li> --}}
