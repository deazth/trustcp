<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->

@can('admin-menu-list')
<li class="nav-title">Admin Menu</li>

@can('menu-sys-config')
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-cog"></i> System Configs</a>
	<ul class="nav-dropdown-items">

		<li class='nav-item nav-link'><i class='nav-icon la la-server'></i> {{ gethostname() }}</li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('commonconfig') }}'><i class='nav-icon la la-cog'></i> CommonConfigs</a></li>
	  {{-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('setting') }}'><i class='nav-icon la la-cog'></i> <span>Settings</span></a></li> --}}
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon la la-terminal'></i> Logs</a></li>
		<li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}"><i class="nav-icon la la-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('push-noti-history') }}'><i class='nav-icon la la-question'></i> Push noti histories</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('guide') }}'><i class='nav-icon la la-question'></i> Guides</a></li>

		<li class='nav-item'><a class='nav-link' href='{{ route('suppa.index') }}'><i class='nav-icon la la-question'></i> Power Root</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ route('suppa.findldap') }}'><i class='nav-icon la la-question'></i> LDAP Finder</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('idea-category') }}'><i class='nav-icon la la-question'></i> Idea categories</a></li>
	</ul>
</li>
@endcan

<!-- broadcast -->
@can('menu-broadcast')
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-bullhorn"></i> Broadcast</a>
	<ul class="nav-dropdown-items">
	@can('bc-announcement')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('announcement') }}'><i class='nav-icon la la-bullhorn'></i> Announcements</a></li>
	@endcan
	@can('bc-news')
		<li class='nav-item'><a class='nav-link' href="{{ backpack_url('news') }}"><i class='nav-icon la la-user-edit'></i> News</a></li>
	@endcan
	</ul>
</li>
@endcan

<!-- Diary LOVs -->
@can('menu-diary-config')
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-book"></i> <span>Diary Configs</span></a>
	<ul class="nav-dropdown-items">
	@can('diary-div-group')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('compgroup') }}'><i class='nav-icon la la-user-clock'></i> Div Group</a></li>
	@endcan
	@can('diary-group-lov')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('lovgp') }}'><i class='nav-icon la la-user-tag'></i> Group LOV</a></li>
	@endcan
	@can('diary-tags')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('taskcategory') }}'><i class='nav-icon la la-tag'></i> Activity Tags</a></li>
	@endcan
	@can('diary-types')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('activitytype') }}'><i class='nav-icon la la-tasks'></i> Activity Types</a></li>
	@endcan
	@can('diary-subtype')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('ctactsubtype') }}'><i class='nav-icon la la-tasks'></i> Activity Sub Types</a></li>
	@endcan
	@can('diary-leave-types')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('leavetype') }}'><i class='nav-icon la la-clipboard-list'></i> Leave Types</a></li>
	@endcan
	@can('diary-public-hol')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('publicholiday') }}'><i class='nav-icon la la-calendar'></i> PublicHolidays</a></li>
	@endcan
	{{-- @can('diary-staff-leaves')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('staffleave') }}'><i class='nav-icon la la-address-card'></i> Staff Leaves</a></li>
	@endcan --}}
	<li class='nav-item'><a class='nav-link' href='{{ backpack_url('unit') }}'><i class='nav-icon la la-clipboard-list'></i> Units</a></li>

	<li class='nav-item'><a class='nav-link' href='{{ backpack_url('user-team-history') }}'><i class='nav-icon la la-question'></i> User team histories</a></li>
	<li class='nav-item'><a class='nav-link' href='{{ backpack_url('neo-wsr-history') }}'><i class='nav-icon la la-question'></i> Neo wsr histories</a></li>
	<li class='nav-item'><a class='nav-link' href='{{ route('diaryadmin.bulkreset') }}'><i class='nav-icon la la-question'></i> Bulk Reset Expected Hours</a></li>
	</ul>
</li>
@endcan

<!-- Users, Roles, Permissions -->
@can('auth mgmt')
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Authentication</a>
	<ul class="nav-dropdown-items">
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Roles</span></a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>
	</ul>
</li>
@endcan

@can('menu-infra-mgmt')
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-map"></i>Infrastructure Mng</a>
	<ul class="nav-dropdown-items">
	@can('infra-dashboard')
		<li class="nav-item"><a class="nav-link" href="{{ route('sbdash.index') }}"><i class="la la-home nav-icon"></i> Dashboard</a></li>
	@endcan
	@can('infra-building')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('building') }}'><i class='nav-icon la la-building'></i>Building List</a></li>
	@endcan
	@can('infra-floor')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('floor') }}'><i class='nav-icon la la-stream'></i>Floor List</a></li>
	@endcan
	@can('infra-section')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('floorsection') }}'><i class='nav-icon la la-door-open'></i> FloorSections</a></li>
	@endcan
	@can('infra-seat')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('seat') }}'><i class='nav-icon la la-chair'></i> Seats</a></li>
	@endcan
	@can('infra-meeting-area')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('meetingarea') }}'><i class='nav-icon la la-toilet-paper'></i> Meeting Areas</a></li>
	@endcan
	@can('infra-equipment-type')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('equipmenttype') }}'><i class='nav-icon la la-fire-extinguisher'></i> Equipment Types</a></li>
	@endcan
	@can('infra-area-booking')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('areabooking') }}'><i class='nav-icon lab la-accessible-icon'></i> Area Bookings</a></li>
	@endcan
	</ul>
</li>
@endcan

@can('menu-skillset-mgmt')
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-gear"></i>Skillset Mgmt</a>
	<ul class="nav-dropdown-items">
		@can('skill-category')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('skillcategory') }}'><i class='nav-icon la la-cat'></i> Skill Categories</a></li>
		@endcan
		@can('skill-type')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('skilltype') }}'><i class='nav-icon la la-apple'></i> Skill Types</a></li>
		@endcan
		@can('skill-list')
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('commonskillset') }}'><i class='nav-icon la la-question'></i> Common Skillsets</a></li>
		@endcan
	</ul>
</li>
@endcan

@can('skill-admin')
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Skill / Involvement</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('pers-job-type') }}'><i class='nav-icon la la-question'></i> Pers job types</a></li>
		{{-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('bau-exp-group') }}'><i class='nav-icon la la-question'></i> Bau exp groups</a></li> --}}
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('bau-exp-type') }}'><i class='nav-icon la la-question'></i> Bau exp types</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('jobscope') }}'><i class='nav-icon la la-question'></i> Jobscopes</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('bau-experience') }}'><i class='nav-icon la la-question'></i> Bau experiences</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ route('sa.inv.stats') }}'><i class="nav-icon lar la-chart-bar"></i> Involvement Stats</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ route('sa.inv.list') }}'><i class="nav-icon las la-list-ol"></i> Involvement List</a></li>
	</ul>
</li>
@endcan

@endcan
{{-- end admin menu list --}}


<li class="nav-title">User Menu</li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> Home</a></li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-building"></i> Agile Office</a>
	<ul class="nav-dropdown-items">
		<li class='nav-item'><a class='nav-link' href='{{ route('inventory.seat.showbuilding') }}'><i class='nav-icon las la-clipboard-list'></i> Current Seat Availability</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('reserveseat') }}'><i class='nav-icon la la-clipboard-check'></i> Seat Reservations</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ route('inv.landing') }}'><i class="nav-icon las la-qrcode"></i> Workspace Check-in</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('seatcheckin') }}'><i class='nav-icon la la-history'></i> Check-in History</a></li>
		{{-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('userareabooking') }}'><i class='nav-icon la la-users'></i> Meeting Area Reservation</a></li> --}}
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#" style="font-size: 15px;"><i class="nav-icon las la-map-pin"></i>Work From Anywhere</a>
	<ul class="nav-dropdown-items">
		<li class='nav-item'><a class='nav-link' href='{{ route('locationhistory.checkinloc') }}'><i class='nav-icon la la-user-check'></i> Location Check-In</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('locationhistory') }}'><i class='nav-icon la la-map-marked'></i> Location History</a></li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-book"></i> Diary</a>
	<ul class="nav-dropdown-items">
	<li class='nav-item'><a class='nav-link' href='{{ route('diary.overview') }}'><i class='nav-icon lar la-chart-bar'></i> Overview</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('gwdactivity') }}'><i class='nav-icon las la-book-open'></i> Diary Entries</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('dailyperformance') }}'><i class="nav-icon la la-clipboard-list"></i> Daily Summary</a></li>
	</ul>
</li>

@if(backpack_user()->isGITD())
	<li class="nav-item nav-dropdown">
		<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-user-cog"></i> Self Declaration</a>
		<ul class="nav-dropdown-items">
			<li class='nav-item'><a class='nav-link' href='{{ backpack_url("personalskillset")}}?status=true'><i class='nav-icon la la-user-cog'></i> My Skillset</a></li>
			<li class='nav-item'><a class='nav-link' href='{{ route("ind.jobcatform") }}'><i class='nav-icon la la-user-cog'></i> My Job Category</a></li>
			<li class='nav-item'><a class='nav-link' href='{{ backpack_url("involvement")}}'><i class='nav-icon la la-user-cog'></i> My Involvement</a></li>
		</ul>
	</li>
@endif

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-award"></i> Appreciation Cards</a>
	<ul class="nav-dropdown-items">
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('appreciatecard') }}'><i class='nav-icon las la-sign-out-alt'></i> Send Cards</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('receivedcard') }}'><i class='nav-icon las la-sign-in-alt'></i> Received Cards</a></li>
	</ul>
</li>

<li class="nav-title">Subordinate Menu</li>
<li class='nav-item'><a class='nav-link' href="{{ route('team.index') }}"><i class='nav-icon la la-users'></i> My Subordinates</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('team.lastloc') }}"><i class='nav-icon la la-map-marked'></i> Last Locations</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('team.checkinout') }}"><i class='nav-icon las la-user-clock'></i> Check In / Out</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('team.diaryperf') }}"><i class='nav-icon la la-book'></i> Diary Performance</a></li>

<li class="nav-title">Miscellaneous</li>
<li class='nav-item'><a class='nav-link' href='{{ route('app.list') }}'><i class="nav-icon las la-cloud-download-alt"></i> Download Mobile App</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('news.overview') }}"><i class='nav-icon la la-newspaper'></i> User News</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('caretaker') }}'><i class='nav-icon la la-user-md'></i> Caretaker Mgmt</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('smile') }}"><i class='nav-icon la la-smile'></i> SMILE</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('tribe.index') }}"><i class='nav-icon la la-compass'></i> TRIBE</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('tribe.detect') }}"><i class='nav-icon la la-compass'></i> DETECT</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('uguide.index') }}"><i class='nav-icon las la-blind'></i> User Guides</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('staff.finder') }}"><i class='nav-icon las la-search'></i> Staff Finder</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('ideabox') }}'><i class='nav-icon las la-box-open'></i> Idea Box</a></li>
<li class='nav-item'><a class='nav-link' href="{{ route('userstat.index') }}"><i class='nav-icon las la-chart-bar'></i> Statistics</a></li>

@can('super-admin')
<li class="nav-title">Under Development</li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('attendance') }}'><i class='nav-icon la la-question'></i> Attendances</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('batch-diary-report') }}'><i class='nav-icon la la-question'></i> Batch diary reports</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('leave-information') }}'><i class='nav-icon la la-question'></i> Leave informations</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('user-stat-history') }}'><i class='nav-icon la la-question'></i> User stat histories</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('assistant') }}'><i class='nav-icon la la-question'></i> Assistants</a></li>
@endcan
