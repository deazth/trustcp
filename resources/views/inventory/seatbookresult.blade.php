@extends(backpack_view('blank'))

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Seat Reservation</span>
        <small>Bookable seat.</small>
        <small><a href="{{ backpack_url('reserveseat') }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> <span>Back to my reservations</span></a></small>
	  </h2>
	</section>
@endsection

@section('content')
  <div class="row">
  	<div class="col-md-12">
  		<!-- Default box -->

  		@include('crud::inc.grouped_errors')

			<div class="card">
				<div class="card-body">
					<label>Search Parameters</label>
					<p class="card-text">
						Building: {{ $buildname }}<br />
						From : {{ $stime }}<br />
						To : {{ $etime }}
					</p>
				</div>
			</div>

  		  <form method="post"
  		  		action="{{ route('userseatbook.dobooking') }}">
  			  {!! csrf_field() !!}
  		      <input type="hidden" name="stime" value="{{ $stime }}" />
						<input type="hidden" name="etime" value="{{ $etime }}" />

						<div class="card">
							<div class="card-body">
								@if(sizeof($seatlist) == 0)
								<pre class="card-text text-center">No available seat for booking at that timeslot</pre>
								@else
								<p class="card-text">Available Seats. Select one to reserve</p>
								<div role="tablist" id="accordionBuild">
									@foreach($seatlist as $key=> $as)
									<div class="card mb-0">
                    <div class="card-header" id="headingb{{ $key }}" role="tab">
											<div class="row">
												<div class="col-7">
													<h5 class="mb-0"><a class="text-white" data-toggle="collapse" href="#collapseb{{ $key }}" aria-expanded="true" aria-controls="collapseb{{ $key }}">{{ $as['name'] }} ({{ $as['count'] }})</a></h5>
												</div>
												<div class="col-5">
													@if($as['gotlayout'])
													<h5 class="mb-0 float-right"><a href="{{route('inventory.floor.getlayout', ['id' => $key])}}" target="_blank">View Layout</a></h5>
													@endif
												</div>
											</div>


                    </div>
                    <div class="collapse" id="collapseb{{ $key }}" role="tabpanel" aria-labelledby="headingb{{ $key }}" data-parent="#accordionBuild">
                      <div class="card-body p-1">
												<div role="tablist" id="accordionFloor">
													@foreach($as['fcs'] as $keyf => $fc)
													<div class="card bg-light mb-0 ">
				                    <div class="card-header" id="headingf{{ $keyf }}" role="tab">
															<div class="row">
																<div class="col-7">
																	<h5 class="mb-0"><a class="text-dark" data-toggle="collapse" href="#collapsef{{ $keyf }}" aria-expanded="true" aria-controls="collapsef{{ $keyf }}">{{ $fc['name'] }} ({{ $fc['count'] }})</a></h5>
																</div>
																<div class="col-5">
																	@if($fc['gotlayout'])
																	<h5 class="mb-0 float-right"><a href="{{route('inventory.fc.getlayout', ['id' => $keyf])}}" target="_blank">View Layout</a></h5>
																	@endif
																</div>
															</div>
				                    </div>
				                    <div class="collapse" id="collapsef{{ $keyf }}" role="tabpanel" aria-labelledby="headingf{{ $keyf }}" data-parent="#accordionFloor">
				                      <div class="card-body p-1">
																<div class="row">
																	@foreach ($fc['seats'] as $keys => $seat)
																	<div class="col-auto m-1">
																		<button type="submit" name="seat_id" value="{{ $keys }}" class="btn btn-primary text-bold d-flex justify-content-between align-items-center"><span class="font-weight-bold">{{ $seat['name'] }}</span> <i class="las la-sign-in-alt"></i></button>
																	</div>
																	@endforeach
																</div>
															</div>
				                    </div>
				                  </div>
													@endforeach
												</div>
											</div>
                    </div>
                  </div>
									@endforeach
								</div>



									{{-- <div class="row">
										@foreach($seatlist as $as)
											<div class="col-sm-6 p-1 col-md-4">
												<div class="card m-1">
											    <div class="card-body">
											      <div class="text-value">{{ $as['seat']->label }}</div>
											      <div>{{ $as['free'] }} seat(s) available.</div>
														<div class="progress bg-success progress-xs my-2">
														@if($as['perc'] >= 90)
															<div class="progress-bar bg-danger" role="progressbar" style="width: {{ $as['perc']  }}%" aria-valuenow="{{ $as['perc']  }}" aria-valuemin="0" aria-valuemax="100"></div>
														@elseif($as['perc'] >= 75)
															<div class="progress-bar bg-warning" role="progressbar" style="width: {{ $as['perc']  }}%" aria-valuenow="{{ $as['perc']  }}" aria-valuemin="0" aria-valuemax="100"></div>
														@else
															<div class="progress-bar bg-info" role="progressbar" style="width: {{ $as['perc']  }}%" aria-valuenow="{{ $as['perc']  }}" aria-valuemin="0" aria-valuemax="100"></div>
														@endif

											      </div>
											      <small class="text-bold">{{ $as['seat']->floor_section->Floor->GetLabel() }}</small>
											    </div>

											    <div class="card-footer px-3 py-2">

											    </div>
											  </div>
											</div>

										@endforeach
									</div> --}}
								@endif
							</div>
						</div>

  		  </form>
  	</div>
  </div>
@stop
