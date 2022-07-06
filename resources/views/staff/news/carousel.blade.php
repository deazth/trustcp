@if (!isset ($jquery) || (isset($jquery) && $jquery == true))
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
@endif

<style>
.scroll {
    max-height: 40em;
    overflow-y: auto;
}


</style>



<div class="row no-gutters">
    <div class="col-md-12 no-gutters">
        <div class="card">
            <div class="card-header">  News </div>
            <div class="card-body scroll no-gutters p-1">
                @foreach($news as $asub)
                <div class="row no-gutters p-0">
                   
                        <div class="list-group col-md-12 no-gutters">

                            <div class="list-group-item list-group-item-action ">
                            <a  href="{{route('news.byid',['newsId' => $asub->id])}}"> {{ $asub->title }} </a></div>



                        </div>
                   
                </div> <!-- row -->
                @endforeach
                <a href="{{route('news.overview')}}">View More</a>
            </div>
        </div>
    </div> <!-- col-12 -->
</div>









{{--$news--}}