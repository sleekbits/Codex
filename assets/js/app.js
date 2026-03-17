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
      current.delete(key);
      existing.filter(v => v !== value).forEach(v => current.append(key, v));
      $(this).removeClass('active');
    } else {
      current.append(key, value);
      $(this).addClass('active');
    }
    history.replaceState({}, '', '?' + current.toString());
    refreshDashboard(current);
  });

  function refreshDashboard(params){
    if (!$('#dashboardFilters').length) return;
    $.get('/Codex/dashboard/data.php', params.toString(), function(resp){
      if (resp.kpi_html !== undefined) $('#dashboardAjaxTop').html(resp.kpi_html);
      if (resp.chart_html !== undefined) $('#dashboardAjaxCharts').html(resp.chart_html);
    }, 'json');
  }
});
