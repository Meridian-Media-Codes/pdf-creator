jQuery(function($){
  function num(v){
    v = String(v || '').replace(/[^0-9.]/g,'');
    var n = parseFloat(v);
    return isNaN(n) ? 0 : n;
  }

  function getVatRate(){
    var sel = $('#mmim-vat-rate').val();
    if (sel === 'custom') {
      return num($('#mmim-vat-custom').val()) / 100;
    }
    return num(sel) / 100;
  }

  function currency(){
    return $('.mmim-totals').data('currency') || '£';
  }

  function formatMoney(n){
    return currency() + n.toFixed(2);
  }

  function recalc(){
    var subtotal = 0;

    $('#mmim-line-items tbody tr').each(function(){
      var qty = num($(this).find('.mmim-qty').val());
      var unit = num($(this).find('.mmim-unit').val());
      var line = qty * unit;
      subtotal += line;
      $(this).find('.mmim-line-total').text(formatMoney(line));
    });

    var vat = subtotal * getVatRate();
    var total = subtotal + vat;

    $('#mmim-subtotal').text(formatMoney(subtotal));
    $('#mmim-vat').text(formatMoney(vat));
    $('#mmim-total').text(formatMoney(total));
  }

  function toggleVatCustom(){
    var sel = $('#mmim-vat-rate').val();
    if (sel === 'custom') {
      $('#mmim-vat-custom-wrap').show();
    } else {
      $('#mmim-vat-custom-wrap').hide();
    }
    recalc();
  }

  $('#mmim-line-items').on('input', '.mmim-qty, .mmim-unit, .mmim-desc', recalc);
  $('#mmim-vat-rate').on('change', toggleVatCustom);
  $('#mmim-vat-custom').on('input', recalc);

  $('#mmim-add-row').on('click', function(){
    var $tbody = $('#mmim-line-items tbody');
    var idx = $tbody.find('tr').length;

    var row = `
      <tr>
        <td><input class="widefat mmim-desc" name="mmim[line_items][${idx}][desc]" value=""></td>
        <td><input class="widefat mmim-qty" name="mmim[line_items][${idx}][qty]" value="1"></td>
        <td><input class="widefat mmim-unit" name="mmim[line_items][${idx}][unit]" value="0.00"></td>
        <td class="mmim-line-total">${formatMoney(0)}</td>
        <td><button type="button" class="button mmim-remove-row">X</button></td>
      </tr>
    `;
    $tbody.append(row);
    recalc();
  });

  $('#mmim-line-items').on('click', '.mmim-remove-row', function(){
    $(this).closest('tr').remove();
    recalc();
  });

  toggleVatCustom();
  recalc();
});
