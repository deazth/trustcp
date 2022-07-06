<table class="table table-sm table-borderless">
    <colgroup>
        <col />
        <col />
        <col />
      

    </colgroup>
    <tr>
        <th>Date</th>
        <td>:</td>
        <td>{{$checkin->created_at}}</td>


    </tr>
    <tr>
        <th>Checkin Location</th>
        <td>:</td>
        <td>{{$checkin->address}}</td>
   

    </tr>
    <tr>
        <th>Current Booking</th>
        <td>:</td>
        <td></td>
     

    </tr>
    <tr>
        <th>Future Booking</th>
        <td>:</td>
        <td></td>


    </tr>

</table>

<a href="{{ route('locationhistory.checkinloc')}}" class="btn btn-primary " role="button"> Update Location </a>
<a href="{{ route('inv.landing')}}" class="btn btn-primary " role="button"> Agile Office </a>