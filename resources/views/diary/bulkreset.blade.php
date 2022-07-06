@extends(backpack_view('blank'))

@section('title', 'Bulk Diary Expected Hours Reset')

@section('page-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')


<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card mb-3">
      <div class="card-header">Bulk Reset</div>
      <div class="card-body">
        <form method="GET" action="{{ route('diaryadmin.bulkreset', [], false) }}" id="whform">
          <!-- @csrf -->
          <!-- <h5 class="card-title">Date range</h5> -->
          <div class="form-group row">
            <label for="sinput" class="col-md-4 col-form-label text-md-right">Date</label>
            <div class="col-md-6">
              <input id="sinput" class="form-control" type="date" name="indate" value="{{ $indate }}" required max="{{ $maxdate }}" autofocus>
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

@if($result)
<div class="col-md-12">
  <div class="card">
    <div class="card-header">Existing Entry Summary for {{ $indate }}</div>
    <div class="card-body">
      <!-- <h5 class="card-title">List of task type</h5> -->
      <form method="post" action="{{ route('diaryadmin.dobulkreset', [], false) }}">
      <div class="table-responsive">
        <table id="taskdetailtable" class="table table-striped table-hover">
          <thead>
            <tr>
              <th scope="col">Remark</th>
              <th scope="col">Count</th>
              <th scope="col">Reset?</th>
            </tr>
          </thead>
          <tbody>
            @foreach($result as $atask)
            <tr>
              <td>{{ $atask->remark ?? 'null' }}</td>
              <td>{{ $atask->rcount }}</td>
              <td><input type="checkbox" name="remarks[]" value="{{ $atask->remark ?? '' }}" /></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <hr />

        @csrf
        <input type="hidden" name="indate" value="{{ $indate }}" />
        <div class="form-group row">
          <div class="col-md-6 offset-md-4">
            <button type="submit" class="btn btn-primary">Reset {{ $indate }}</button>
          </div>
        </div>
      </form>
    </div>
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
