@if ($crud->hasAccess('cancel') && ($entry->status == 'Active' || $entry->status == 'Pending SB'))
	<a href="javascript:void(0)" onclick="cancelEntry(this)" data-route="{{ url($crud->route.'/'.$entry->getKey().'/cancel') }}" class="btn btn-sm btn-link" data-button-type="clone" title="Cancel the booking"><i class="la la-calendar-times"></i> Cancel</a>
@endif

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
	if (typeof cancelEntry != 'function') {
	  $("[data-button-type=clone]").unbind('click');

	  function cancelEntry(button) {
	      // ask for confirmation before deleting an item
	      // e.preventDefault();
	      var button = $(button);
	      var route = button.attr('data-route');

          $.ajax({
              url: route,
              type: 'GET',
              success: function(result) {
                  // Show an alert with the result
                  new Noty({
                    type: "success",
                    text: "Booking cancelled"
                  }).show();

                  // Hide the modal, if any
                  $('.modal').modal('hide');

                  if (typeof crud !== 'undefined') {
                    crud.table.draw(false);
                  }
              },
              error: function(result) {
                  // Show an alert with the result
									alert(JSON.stringify(result));
                  new Noty({
                    type: "warning",
                    text: "<strong>Failed to cancel</strong>"
                  }).show();
              }
          });
      }
	}

	// make it so that the function above is run after each DataTable draw event
	// crud.addFunctionToDataTablesDrawEventQueue('cancelEntry');
</script>
@if (!request()->ajax()) @endpush @endif
