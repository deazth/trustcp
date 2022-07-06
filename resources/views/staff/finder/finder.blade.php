@extends(backpack_view('blank'))

@section('title', 'Staff Finder')

@section('page-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')


<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card mb-3">
      <div class="card-header">Find Staff</div>
      <div class="card-body">
        <form method="GET" action="{{ route('staff.finder', [], false) }}" id="whform">
          <!-- @csrf -->
          <!-- <h5 class="card-title">Date range</h5> -->
          <div class="form-group row">
            <label for="sinput" class="col-md-4 col-form-label text-md-right">Name / Staff No</label>
            <div class="col-md-6">
              <input id="sinput" class="form-control" type="text" name="input" required minlength="3" value="{{ $initialval }}" autofocus>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-6 offset-md-4">
              <button type="submit" class="btn btn-primary">Search</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>
</div>

@if($result != 'empty')
<div class="col-md-12">
  <div class="card">
    @if($result == '404')
    <div class="alert alert-error" role="alert">No result</div>
    @else
    <div class="card-header">Search Result</div>
    <div class="card-body">
      <!-- <h5 class="card-title">List of task type</h5> -->
      <div class="table-responsive">
        <table id="taskdetailtable" class="table table-striped table-hover">
          <thead>
            <tr>
              <th scope="col">Staff ID</th>
              <th scope="col">Name</th>
              <th scope="col">Division</th>
              <th scope="col">Email</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($result as $atask)
            <tr>
              <td><a href="{{route('staff.detail',[$atask->id])}}">{{ $atask->staff_no }}</td>
              <td>{{ $atask->name }}</td>
              <td>{{ $atask->unit }}</td>
              <td>{{ $atask->email }}</td>
              <td>{{ $atask->status ? 'Active' : 'Inactive' }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th scope="col">Staff ID</th>
              <th scope="col">Name</th>
              <th scope="col">Division</th>
              <th scope="col">Email</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    @endif
  </div>
</div>
@endif
</div>
</div>

@endsection

@section('page-js')

<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
  $(document).ready(function() {
    $('#taskdetailtable').DataTable();

    $('#skid').select2({
      width: '100%'
    });

    $('#expid').select2({
      width: '100%'
    });
  });
</script>
@endsection
