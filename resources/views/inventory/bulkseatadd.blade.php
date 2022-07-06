@extends(backpack_view('blank'))

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">Bulk Seat Add</span>
        <small>Add multiple individual seats.</small>
        <small><a href="{{ backpack_url('seat') }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> <span>Back to seats</span></a></small>
	  </h2>
	</section>
@endsection

@section('content')
  <div class="row">
  	<div class="{{ $crud->getCreateContentClass() }}">
  		<!-- Default box -->

  		@include('crud::inc.grouped_errors')

  		  <form method="post"
  		  		action="{{ route('inv.seat.dobulkadd') }}">
  			  {!! csrf_field() !!}
  		      <!-- load the view from the application if it exists, otherwise load the one in the package -->
  		      @if(view()->exists('vendor.backpack.crud.form_content'))
  		      	@include('vendor.backpack.crud.form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
  		      @else
  		      	@include('crud::form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
  		      @endif

            <div id="saveActions" class="form-group">
                <button type="submit" class="btn btn-success">
                    <span class="las la-cart-plus" role="presentation" aria-hidden="true"> Add Seats</span>
                </button>
            </div>
  		  </form>
  	</div>
  </div>
@stop
