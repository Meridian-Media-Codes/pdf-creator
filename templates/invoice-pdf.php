<?php
if (!defined('ABSPATH')) exit;

$settings = MMIM_Settings::get_settings();

$inv_no = get_post_meta($invoice_id, '_mmim_invoice_number', true);
$issue = get_post_meta($invoice_id, '_mmim_issue_date', true);
$due = get_post_meta($invoice_id, '_mmim_due_date', true);

$bill_name = get_post_meta($invoice_id, '_mmim_bill_to_name', true);
$bill_email = get_post_meta($invoice_id, '_mmim_bill_to_email', true);
$bill_addr = get_post_meta($invoice_id, '_mmim_bill_to_address', true);

$vat_rate = get_post_meta($invoice_id, '_mmim_vat_rate', true);
$vat_custom = get_post_meta($invoice_id, '_mmim_vat_custom', true);

$items = get_post_meta($invoice_id, '_mmim_line_items', true);
$items = is_array($items) ? $items : [];

$currency = $settings['currency_symbol'] ?: '£';

$vat_percent = 0;
if ($vat_rate === 'custom') {
  $vat_percent = floatval($vat_custom);
} else {
  $vat_percent = floatval($vat_rate);
}
$vat_decimal = max(0, $vat_percent) / 100;

$subtotal = 0;
foreach ($items as $r) {
  $qty = floatval(preg_replace('/[^0-9.]/','', $r['qty'] ?? '1'));
  $unit = floatval(preg_replace('/[^0-9.]/','', $r['unit'] ?? '0'));
  $subtotal += ($qty * $unit);
}
$vat_amount = $subtotal * $vat_decimal;
$total = $subtotal + $vat_amount;

$logo = get_site_icon_url(256);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
    .top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 18px; }
    .logo { width: 64px; height: 64px; }
    h1 { font-size: 20px; margin: 0 0 6px; }
    .box { border:1px solid #ddd; padding:10px; border-radius:6px; }
    .grid { display:flex; gap:10px; }
    .grid > div { flex:1; }
    table { width:100%; border-collapse:collapse; margin-top: 14px; }
    th, td { border-bottom:1px solid #eee; padding:8px 6px; text-align:left; vertical-align:top; }
    th { background:#f7f7f7; }
    .right { text-align:right; }
    .totals { width: 260px; margin-left:auto; margin-top: 12px; }
    .totals div { display:flex; justify-content:space-between; padding:6px 0; border-top:1px solid #eee; }
    .totals .grand { font-size: 14px; font-weight: bold; }
    .muted { color:#555; }
    .footer { margin-top: 16px; }
  </style>
</head>
<body>

  
    <table width="100%" style="margin-bottom:18px;">
  <tr>
    <td style="vertical-align:top;">
      <h1 style="margin:0 0 6px;">Invoice</h1>
      <div class="muted">Invoice number: <?php echo esc_html($inv_no ?: $invoice_id); ?></div>
      <div class="muted">Issue date: <?php echo esc_html($issue); ?></div>
      <?php if ($due): ?>
        <div class="muted">Due date: <?php echo esc_html($due); ?></div>
      <?php endif; ?>
    </td>

    <td style="text-align:right; vertical-align:top;">
      <?php if ($logo): ?>
        <img src="<?php echo esc_url($logo); ?>" style="width:90px; height:auto;">
      <?php endif; ?>
    </td>
  </tr>
</table>


  <div class="grid">
    <div class="box">
      <strong>From</strong><br>
      <?php echo esc_html($settings['business_name']); ?><br>
      <?php echo nl2br(esc_html($settings['business_address'])); ?><br>
      <?php if (!empty($settings['business_email'])): ?><?php echo esc_html($settings['business_email']); ?><br><?php endif; ?>
      <?php if (!empty($settings['business_phone'])): ?><?php echo esc_html($settings['business_phone']); ?><br><?php endif; ?>
      <?php if (!empty($settings['vat_number'])): ?>VAT: <?php echo esc_html($settings['vat_number']); ?><?php endif; ?>
    </div>

    <div class="box">
      <strong>Bill to</strong><br>
      <?php echo esc_html($bill_name); ?><br>
      <?php if ($bill_email): ?><?php echo esc_html($bill_email); ?><br><?php endif; ?>
      <?php echo nl2br(esc_html($bill_addr)); ?>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Description</th>
        <th class="right">Qty</th>
        <th class="right">Unit</th>
        <th class="right">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $r):
        $desc = $r['desc'] ?? '';
        $qty = floatval(preg_replace('/[^0-9.]/','', $r['qty'] ?? '1'));
        $unit = floatval(preg_replace('/[^0-9.]/','', $r['unit'] ?? '0'));
        $line = $qty * $unit;
      ?>
      <tr>
        <td><?php echo esc_html($desc); ?></td>
        <td class="right"><?php echo esc_html(rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.')); ?></td>
        <td class="right"><?php echo esc_html($currency . number_format($unit, 2)); ?></td>
        <td class="right"><?php echo esc_html($currency . number_format($line, 2)); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <table style="width:260px; margin-left:auto; margin-top:12px; border-collapse:collapse;">
  <tr>
    <td style="width:150px; padding:6px 0; text-align:left;">Subtotal</td>
    <td style="width:110px; padding:6px 0; text-align:right; font-weight:bold;">
      <?php echo esc_html($currency . number_format($subtotal, 2)); ?>
    </td>
  </tr>

  <tr>
    <td style="width:150px; padding:6px 0; text-align:left;">
      VAT (<?php echo esc_html(number_format($vat_percent, 2)); ?>%)
    </td>
    <td style="width:110px; padding:6px 0; text-align:right; font-weight:bold;">
      <?php echo esc_html($currency . number_format($vat_amount, 2)); ?>
    </td>
  </tr>

  <tr>
    <td style="width:150px; padding:8px 0; text-align:left; font-size:14px; font-weight:bold; border-top:1px solid #ddd;">
      Total
    </td>
    <td style="width:110px; padding:8px 0; text-align:right; font-size:14px; font-weight:bold; border-top:1px solid #ddd;">
      <?php echo esc_html($currency . number_format($total, 2)); ?>
    </td>
  </tr>
</table>



  <div class="footer">
    <?php if (!empty($settings['bank_account_name']) || !empty($settings['bank_account_number']) || !empty($settings['bank_sort_code'])): ?>
      <div class="box">
        <strong>Bank details</strong><br>
        <?php if (!empty($settings['bank_account_name'])): ?>Account name: <?php echo esc_html($settings['bank_account_name']); ?><br><?php endif; ?>
        <?php if (!empty($settings['bank_account_number'])): ?>Account number: <?php echo esc_html($settings['bank_account_number']); ?><br><?php endif; ?>
        <?php if (!empty($settings['bank_sort_code'])): ?>Sort code: <?php echo esc_html($settings['bank_sort_code']); ?><br><?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($settings['default_terms']) || !empty($settings['default_notes'])): ?>
      <div style="margin-top:10px;" class="box">
        <?php if (!empty($settings['default_terms'])): ?><strong>Terms</strong><br><?php echo nl2br(esc_html($settings['default_terms'])); ?><br><br><?php endif; ?>
        <?php if (!empty($settings['default_notes'])): ?><strong>Notes</strong><br><?php echo nl2br(esc_html($settings['default_notes'])); ?><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
