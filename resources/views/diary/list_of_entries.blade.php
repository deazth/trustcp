@if (!isset ($jquery) || (isset($jquery) && $jquery == true))
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
@endif


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.11.1/fh-3.1.9/sl-1.3.3/datatables.min.css" />


<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.11.1/fh-3.1.9/sl-1.3.3/datatables.min.js">
</script>

<script type="text/javascript" src="/js/datatable/datetime.js">
</script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.11/sorting/date-eu.js">
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

<style>
    td {
        white-space: normal !important;
    }
</style>

<div class="card m-1 no-gutters ">
    <div class="card-header">

        Diary Entries

    </div>

    <div class="card-body">
        <div class="table-responsive">

            <table id="example" class="table table-sm cell-border table-bordered" style="white-space: nowrap;width:100%">
                <colgroup>
                <col style="width: 5em;" >
                    <col style="width: 3em;" >
                    <col style="width: 5em;" >
                    <col/>
                    <col/>
                    <col/>
                    <col style="width: 3em;" >
                </colgroup>
                <thead>
                    <tr>
                        <th>Entry Date</th>
                        <th>Hours</th>
                        <th>Tag</th>
                        <th>Type</th>
                        <th>ID / Title</th>
                        <th>Details</th>
                        <th>Action</th>

                    </tr>
                </thead>

            </table>
        </div>
    </div>

    <div class="card-footer">
        <div class="float-left no-gutter">

            <div class="btn-group no-gutter" role="group">
               <button type="button" class="btn bg-info" id="acthourbutton" disabled>Actual hour: <span id="acthourtext"></span></button>
                <button type="button" class="btn bg-info" id="expthourbutton" disabled>Expected hour: <span id="expthourtext"></span></button>
                

            </div>


        </div>
        <div class="float-right">




            <button class="btn btn-sm btn-info" id="resetButton" title="Reset day-type / expected hours using data from NEO">
                <i class="la la-refresh"></i>
            </button>

        </div>

    </div>




</div>
<script>
    var uid = "{{$field['uid']}}";
</script>

<script>
    var ad = $("#activity_date_id").val();
    $(document).ready(function() {
        ad = $("#activity_date_id").val();

        $("#ac_dt_id").val(ad);
        getPerf(uid, ad);

    });


    var tableEntry =
        $('#example').DataTable({
            "processing": false,
            "ajax": "/v2/gwd/list/" + uid + "/" + ad,
            "columns": [{
                    "data": "created_at"
                },
                {
                    "data": "hours_spent"
                },
                {
                    "data": "activity_tag.descr"
                },
                {
                    "data": "activity_type.descr"
                },
                {
                    "data": "parent_number"
                },
                {
                    "data": "details"
                },
                {
                    "data": null,
                    "bSortable": false,
                    "mRender": function(o) {
                        return '<a href="/v2/gwdactivity/' + o.id + '/edit">' + 'Edit' + '</a>';
                    }
                }


            ],

            columnDefs: [{
                targets: 0,
                render: function(data) {
                    return moment(data).format('YYYY-MM-DD');
                }
            }]
        });

    $("#activity_date_id").change(
        function(event) {
            ad = $("#activity_date_id").val();

            tableEntry.ajax.url("/v2/gwd/list/" + uid + "/" + ad).load();
            $("#ac_dt_id").val(ad);

            getPerf(uid, ad);

        }

    );

    $("#resetButton").click(function() {

        var values = {
            'ac_dt_id': ad

        };

        $.ajax({
            url: "{{route('staff.df.reset')}}",
            type: "POST",
            data: values,
        });
    });


    function getPerf(uid, dt) {
        var urlPerf = "{{route('diary.perfByDate',[':uidVar',':dateVar'])}}"
        urlPerf = urlPerf.replace(':uidVar', uid);
        urlPerf = urlPerf.replace(':dateVar', ad);
        $.ajax({
            url: urlPerf,
            type: "get",
            success: function(data) {


                $("#expthourbutton").attr("class", "btn nohover " + data.bg);
                $("#acthourbutton").attr("class", "btn nohover " + data.bg);
                $("#expthourtext").html(data.todaydf.expected_hours);
                $("#acthourtext").html(data.todaydf.actual_hours);


            }

        });
    }
</script>