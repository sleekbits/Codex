$(function(){
  if ($('#trackingTable').length) {
    $('#trackingTable').DataTable({pageLength:25, order: []});
  }

  $('#selectAllRows').on('change', function(){
    $('.row-check').prop('checked', $(this).is(':checked'));
    toggleBulkToolbar();
  });

  $(document).on('change', '.row-check', toggleBulkToolbar);

  function toggleBulkToolbar(){
    const count = $('.row-check:checked').length;
    $('#bulkCount').text(count);
    $('#bulkToolbar').toggleClass('d-none', count === 0);
  }

  $('#dashboardFilters').on('click', '.slicer-tile', function(){
    const key = $(this).data('filter');
    const value = $(this).data('value');
    const current = new URLSearchParams(window.location.search);
    if (current.get(key) == String(value)) current.delete(key); else current.set(key, value);
    if (key === 'year') current.delete('month');
    history.replaceState({}, '', '?' + current.toString());
    refreshDashboard(current);
  });

  function refreshDashboard(params){
    if (!$('#dashboardAjax').length) return;
    $.get('/Codex/dashboard/data.php', params.toString(), function(resp){
      $('#dashboardAjax').html(resp.html);
    }, 'json');
  }
});
