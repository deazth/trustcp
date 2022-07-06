@if (!isset ($jquery) || (isset($jquery) && $jquery == true))
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
@endif


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.11.1/fh-3.1.9/sl-1.3.3/datatables.min.css" />


<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.11.1/fh-3.1.9/sl-1.3.3/datatables.min.js">
</script>
{{-- <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.11.1/dataRender/datetime.js">
</script> --}}
<script type="text/javascript" src="/js/datatable/datetime.js">
</script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.11/sorting/date-eu.js">
</script>


<style>
    select.form-control {
        display: inline;
        width: 200px;
        margin-left: 25px;
    }
</style>

<div class="card m-1 no-gutters ">
    <div class="card-header">

        List of TRIBE Assignment

    </div>
    <div id="collapseOne">
        <div class="card-body">
        <div class="category-filter float-left">
                    <select id="categoryFilter" class="form-control">
                        <option value="">Show All Status</option>
                        <option value="In Progress" selected>In Progress</option>
                        <option value="Completed">Completed</option>
                        <option value="Not Started yet">Not Started yet</option>
                    </select>
                </div>
            <div class="table-responsive" style="min-height:20em">


                <table id="assdetailtable" class="table display table-sm" style="white-space: nowrap;">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col" class="d-none d-sm-table-cell">Change No</th>
                            <th scope="col" class="d-none d-sm-table-cell">Project No</th>
                            <th scope="col">Assignment Name</th>
                            <th scope="col">Start Date </th>
                            <th scope="col" class="d-none d-sm-table-cell">End Date</th>
                            <th scope="col">Status</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($field['assignments'] as $acts)
                        <tr>
                            <td>
                                <input type="button" class="tribe_button btn btn-primary btnAss" value="{{ $acts['assignment_id'] }}" />
                                <div class="d-none"> {{ $acts['assignment_id'] }} </div>
                            </td>
                            <td class="ch_no d-none d-sm-table-cell">{{ $acts['change_no'] ?? ''}}</td>
                            <td class="pj_no d-none d-sm-table-cell">{{ $acts['project_no'] ?? ''}}</td>
                            <td class="trb_name">{{ $acts['assignment_name'] }}</td>
                            <td>{{date('d-m-Y', strtotime($acts['date_start'])) }} </td>
                            <td>{{date('d-m-Y', strtotime($acts['date_end'])) }} </td>

                            <td>{{ $acts['status'] }}</td>


                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- <!--<td>{{ $acts['date_start'] }} </td>--> --}}
                {{-- <!--<td>{{ date('d-m-Y', strtotime($acts['date_start'])) }} </td>--> --}}
                {{-- <!-- <td class="d-none d-sm-table-cell">{{ $acts['date_end'] }}</td>--> --}}
            </div>
        </div>
    </div>
</div>
<style>

</style>


<script defer>
    $('#assdetailtable').DataTable({
        "lengthMenu": [5, 10, 20, 50],
        "pageLength": 10,
        "ordering": true,
        "columnDefs": [{
            "targets": 4,
            "type": "date-eu"
        }, {
            "targets": 5,
            "type": "date-eu"
        }],
        "searching": true,




    });


    $(document).on('click', '.tribe_button', function(event) {
        event.stopPropagation();
        event.stopImmediatePropagation();
        console.log("here");
        var $item = $(this).closest("tr"); // Finds the closest row <tr>
        var $trbId = $(this).val();
        console.log($trbId);
        var $trbName1 = $item.find(".trb_name").text();
        var $chNo = $item.find(".ch_no").text();
        var $pjNo = $item.find(".pj_no").text();
        var $trbName2 = $chNo.concat(" ", $pjNo);
        var $trbName = $trbName2.concat(" : ", $trbName1);

        console.log($trbId);
        console.log($trbName);
        $("#tribe_assigment_id").val($trbId);
        $("#parent_no").val($trbName);
        $("#tribe_assigment_reset_button").removeClass("d-none");
        event.stopPropagation();
        event.stopImmediatePropagation();
    });




    //Get a reference to the new datatable
    var table = $('#assdetailtable').DataTable();
    //Take the category filter drop down and append it to the datatables_filter div. 
    //You can use this same idea to move the filter anywhere withing the datatable that you want.
    $("#assdetailtable_filter.dataTables_filter").append($("#categoryFilter"));

    //Get the column index for the Category column to be used in the method below ($.fn.dataTable.ext.search.push)
    //This tells datatables what column to filter on when a user selects a value from the dropdown.
    //It's important that the text used here (Category) is the same for used in the header of the column to filter
    var categoryIndex = 6;
    /** I already know the index is 6 -- afdzal */
    /**
    $("#assdetailtable th").each(function(i) {
        if ($($(this)).html() == "Status") {
            categoryIndex = i;
            return false;
        }
    });
    */
    //Use the built in datatables API to filter the existing rows by the Category column
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var selectedItem = $('#categoryFilter').val()
            var category = data[categoryIndex];
            if (selectedItem === "" || category.includes(selectedItem)) {
                return true;
            }
            return false;
        }
    );
    //Set the change event for the Category Filter dropdown to redraw the datatable each time
    //a user selects a new filter.
    $("#categoryFilter").change(function(e) {
        table.draw();
    });

    table.draw();
</script>