@extends(backpack_view('blank'))

@section('title', 'Job Category')

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">My Job Category</span>
        <small>{{ $user->name }}</small>
	  </h2>
	</section>
@endsection

@section('content')
  <div class="row">
  	<div class="{{ $crud->getCreateContentClass() }}">
  		<!-- Default box -->

  		@include('crud::inc.grouped_errors')

  		  <form method="post"
  		  		action="{{ route('ind.jobcatsubmit') }}">
  			  {!! csrf_field() !!}
  		      <!-- load the view from the application if it exists, otherwise load the one in the package -->
  		      @if(view()->exists('vendor.backpack.crud.form_content'))
  		      	@include('vendor.backpack.crud.form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
  		      @else
  		      	@include('crud::form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
  		      @endif

						@if($perm > 0)
            <div id="saveActions" class="form-group">
                <button type="submit" class="btn btn-success">
                    <span class="lar la-edit" role="presentation" aria-hidden="true"> Update</span>
                </button>
            </div>
						@endif
  		  </form>
  	</div>
  </div>
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Job Category Reference</h5>
					<div class="table-responsive">
						<table id="dtabless" class="table table-striped table-hover ">
							<thead>
			          <tr>
									<th scope="col">Category</th>
									<th scope="col">Definition</th>
			          </tr>
			        </thead>
			        <tbody>
								@foreach ($pjt as $key => $value)
									<tr>
										<td>{{ $value->category }}</td>
										<td>{{ $value->definition }}</td>
									</tr>
								@endforeach
			        </tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@stop
