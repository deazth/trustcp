@extends(backpack_view('blank'))

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Meeting Area Finder</span>
        <small>Find available area.</small>
        <small><a href="{{ backpack_url('userareabooking') }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> <span>Back to my bookings</span></a></small>
	  </h2>
	</section>
@endsection

@section('content')
  <div class="row">
  	<div class="{{ $crud->getCreateContentClass() }}">
  		<!-- Default box -->

  		@include('crud::inc.grouped_errors')

  		  <form method="post"
  		  		action="{{ route('userareabooking.searchresult') }}">
  			  {!! csrf_field() !!}
  		      <!-- load the view from the application if it exists, otherwise load the one in the package -->
  		      @if(view()->exists('vendor.backpack.crud.form_content'))
  		      	@include('vendor.backpack.crud.form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
  		      @else
  		      	@include('crud::form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
  		      @endif

            <div id="saveActions" class="form-group">
                <button type="submit" class="btn btn-success">
                    <span class="la la-search" role="presentation" aria-hidden="true"> Search</span>
                </button>
            </div>
  		  </form>
  	</div>
  </div>
@stop
