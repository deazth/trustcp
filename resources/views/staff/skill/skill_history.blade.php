<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/v/bs5/dt-1.11.1/fh-3.1.9/sl-1.3.3/datatables.min.css" />
    

<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.11.1/fh-3.1.9/sl-1.3.3/datatables.min.js">
</script>
<script type="text/javascript" src="/js/datatable/datetime.js">
</script> 
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.11/sorting/date-eu.js">
</script>

<style>
  select.form-control{
    display: inline;
    width: 200px;
    margin-left: 25px;
  }
</style>

@php

$cs = $field['skill'];
$csLvlDesc = $cs->level_desc_arr();

@endphp




<div class="card m-1 no-gutters ">
    <div class="card-header">

        History of skill 
     {{$cs->CommonSkillset->name}}
        for  {{$cs->User->name}}

    </div>
    <div id="collapseOne">
        <div class="card-body">
            <div class="table-responsive" style="min-height:10em">

            <div class="category-filter">

    </div>
                <table id="assdetailtable" class="table display table-sm" style="white-space: nowrap;">
                    <thead>
                        <tr>
                
                            <th scope="col">Remark</th>
                            <th scope="col">Action</th>
                            <!-- <th scope="col" >Previous Level</th> -->
                            <th scope="col">Updated Level</th>
                            <th scope="col">Update On </th>


                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($field['skills'] as $sk)
                        <tr>
                            
                            <td class="ch_no d-none d-sm-table-cell">{{ $sk['remark'] ?? ''}}</td>
                            <td class="pj_no d-none d-sm-table-cell">{{ $sk['action'] ?? ''}}</td>
                            <!--<td class="pj_no d-none d-sm-table-cell">{{ $csLvlDesc[$sk['oldlevel']] ?? ''}}</td>-->
                            <td class="pj_no d-none d-sm-table-cell">{{ $csLvlDesc[$sk['newlevel']] ?? ''}}</td>
                            
                
                            <td>{{date('d/m/Y H:i', strtotime($sk['created_at'])) }} </td>
                    
                            



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
    .btnAss {

        padding: 0.5px !important;
        min-width: 3em;
        margin: 0
    }
</style>


<script defer>
    $('#assdetailtable').DataTable({
        "lengthMenu": [5, 10, 20, 50],
        "pageLength": 20,
        "ordering" : false,

        "searching": true,
        



    });


</script>