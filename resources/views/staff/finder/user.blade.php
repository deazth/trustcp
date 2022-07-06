@extends(backpack_view('blank'))

@section('title', 'Staff Profile')

@section('content')


<div class="container-fluid">
  <div class="row">
    <div class="col">
      <div class="card @if($user->status == 0) bg-secondary @endif">
        <div class="card-body">
          <div class="row">
            <div class="col-md-3 text-center">
                <img class="card-img" style="border: 1px solid #000; max-width:120px; max-height:120px;"
                    src="{{ route('staff.image', ['staff_no' => $user['staff_no']]) }}" loading="lazy">
            </div>
              <div class="col-md-9">
<pre class="mb-0">
Name     : {{ $user['name'] }}
Staffno  : {{ $user['staff_no'] }}
Division : {{ $user['unit'] }}
Unit     : {{ $user['subunit'] }}
Position : {{ $user['position'] }}
Email    : {{ $user['email'] }}
Mobile   : {{ $user['mobile_no'] }}
LOB      : {{ $user['lob_descr'] }}
@if(isset($superior))
Report To: <a href="{{ route('staff.detail', [$superior->id], false) }}">{{ $superior->name }}</a>
      @endif
                  </pre>
              </div>


          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="card">
        <div class="card-header">Personal</div>
        <div class="card-body">
          <a class="btn btn-primary" href="{{route('gwdactivity.index',['uid'=>$user->id])}}" >Diary Entries</a>
          <a class="btn btn-primary" href="{{route('dailyperformance.index',['uid'=>$user->id])}}" >Diary Daily Summary</a>
          <a class="btn btn-primary" href="{{route('locationhistory.index',['uid'=>$user->id])}}" >Location Checkins</a>
          @if($user->isGITD())
          <a class="btn btn-primary" href="{{route('involvement.index',['uid'=>$user->id])}}" >Involvements</a>
          @endif
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div class="card">
        <div class="card-header">Subordinate</div>
        <div class="card-body">
          <a class="btn btn-primary" href="{{route('team.index',['uid'=>$user->id])}}" >List</a>
          <a class="btn btn-primary" href="{{route('team.lastloc',['uid'=>$user->id])}}" >Last Location</a>
          <a class="btn btn-primary" href="{{route('team.checkinout',['uid'=>$user->id])}}" >Check In / Out</a>
          <a class="btn btn-primary" href="{{route('team.diaryperf',['uid'=>$user->id])}}" >Diary Performance</a>
        </div>
      </div>
    </div>
  </div>
  @if($canmod > 2 || ($canmod == 2 && substr($user->staff_no, 0, 1) == 'X'))
  <div class="row">
    <div class="col">
      <div class="card">
        <div class="card-header">Manage {{ $canmod }}</div>
        <div class="card-body">
          <a class="btn btn-primary" href="{{route('staffleave.index',['uid'=>$user->id])}}" >Manual Leave</a>
          <a class="btn btn-primary" target="_blank" href="{{route('ct-user-manage.edit',['id'=>$user->id])}}" >Edit User</a>
        </div>
      </div>
    </div>
  </div>
  @endif
</div> <!-- container -->



@endsection

@section('page-js')

@endsection
