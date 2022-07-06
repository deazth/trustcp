@if ($crud->hasAccess('create'))
  <a href="{{ url($crud->route.'/create') }}" class="btn btn-primary"
    data-style="zoom-in"><span class="ladda-label">
      <i class="la la-mail-bulk"></i> Send Card</span></a>
@endif
