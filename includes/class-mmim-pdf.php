<?php
if (!defined('ABSPATH')) exit;

class MMIM_PDF {
  public static function init() {
    // generation is called from the invoice save handler
  }

  public static function generate_and_store($invoice_id) {
    $vendor = MMIM_PLUGIN_DIR . 'vendor/autoload.php';
    if (!file_exists($vendor)) {
      // Dompdf not installed yet. Install via composer in the plugin folder:
      // composer install --no-dev
      return;
    }
    require_once $vendor;

    $html = self::render_invoice_html($invoice_id);

    $upload = wp_upload_dir();
    $dir = trailingslashit($upload['basedir']) . 'mm-invoices/';
    $url = trailingslashit($upload['baseurl']) . 'mm-invoices/';

    if (!wp_mkdir_p($dir)) return;

    $inv_no = get_post_meta($invoice_id, '_mmim_invoice_number', true);
    $inv_no_safe = $inv_no ? preg_replace('/[^A-Za-z0-9_-]/', '', $inv_no) : (string) $invoice_id;

    $filename = 'invoice-' . $inv_no_safe . '.pdf';
    $filepath = $dir . $filename;

    $dompdf = new \Dompdf\Dompdf([
      'isRemoteEnabled' => true
    ]);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    file_put_contents($filepath, $dompdf->output());

    update_post_meta($invoice_id, '_mmim_pdf_path', $filepath);
    update_post_meta($invoice_id, '_mmim_pdf_url', $url . $filename);
    update_post_meta($invoice_id, '_mmim_pdf_generated_at', current_time('mysql'));
  }

  private static function render_invoice_html($invoice_id) {
    ob_start();
    $template = MMIM_PLUGIN_DIR . 'templates/invoice-pdf.php';
    $invoice_id = intval($invoice_id);
    include $template;
    return ob_get_clean();
  }
}
