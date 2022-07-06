@extends(backpack_view('blank'))

@section('after_styles')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.css" />
@stop

@section('header')
<section class="container-fluid">
    <h2>
        <span class="text-capitalize">News</span>
        <small>{{ $user->name }}</small>
    </h2>
</section>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">



            <div class="row ">
                @foreach($news as $asub)
                  @php
                    if($byID){$clSize = 12;}
                    else {
                      $clSize = $asub->size;
                      if (empty($clSize)) {
                        $clSize = 6;
                      }
                    }
                  @endphp
                <div class="col-md-{{ $clSize }}">
                    <div class="card mb-3">
                        <div class="card-header text-center">{{ $asub->title }}</div>
                        <div class="card-body overflow-auto">
                            {!! $asub->content !!}
                        </div>
                        <div class="card-footer">Posted at {{ $asub->created_at }} {{$asub->id}}</div>
                    </div>
                </div>
                @endforeach
            </div> <!-- row -->



        </div>
    </div>
    @stop

    @section('after_scripts')



    @stop