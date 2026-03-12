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
    const value = String($(this).data('value'));
    const current = new URLSearchParams(window.location.search);
    const existing = current.getAll(key);

    if (existing.includes(value)) {
      const next = existing.filter(v => v !== value);
      current.delete(key);
      next.forEach(v => current.append(key, v));
      $(this).removeClass('active');
    } else {
      current.append(key, value);
      $(this).addClass('active');
    }

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
