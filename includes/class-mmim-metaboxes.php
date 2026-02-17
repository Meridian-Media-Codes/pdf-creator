<?php
if (!defined('ABSPATH')) exit;

class MMIM_Metaboxes {
  public static function init() {
    add_action('add_meta_boxes', [__CLASS__, 'add']);
    add_action('save_post_mm_invoice', [__CLASS__, 'save'], 10, 2);
  }

  public static function add() {
    add_meta_box('mmim_invoice_details', 'Invoice', [__CLASS__, 'render'], 'mm_invoice', 'normal', 'high');
  }

  private static function get_meta($post_id, $key, $default = '') {
    $v = get_post_meta($post_id, $key, true);
    return $v === '' ? $default : $v;
  }

  public static function render($post) {
    $settings = MMIM_Settings::get_settings();

    $invoice_number = self::get_meta($post->ID, '_mmim_invoice_number', '');
    $issue_date = self::get_meta($post->ID, '_mmim_issue_date', date('Y-m-d'));
    $due_date = self::get_meta($post->ID, '_mmim_due_date', '');
    $bill_to_name = self::get_meta($post->ID, '_mmim_bill_to_name', '');
    $bill_to_email = self::get_meta($post->ID, '_mmim_bill_to_email', '');
    $bill_to_address = self::get_meta($post->ID, '_mmim_bill_to_address', '');
    $vat_rate = self::get_meta($post->ID, '_mmim_vat_rate', '20');
    $line_items = get_post_meta($post->ID, '_mmim_line_items', true);
    $line_items = is_array($line_items) ? $line_items : [];

    $pdf_url = self::get_meta($post->ID, '_mmim_pdf_url', '');

    wp_nonce_field('mmim_save_invoice', 'mmim_nonce');
    ?>
    <div class="mmim-wrap">
      <div class="mmim-grid">
        <div class="mmim-card">
          <h3>Bill to</h3>
          <p><label>Name<br><input class="widefat" name="mmim[bill_to_name]" value="<?php echo esc_attr($bill_to_name); ?>"></label></p>
          <p><label>Email<br><input class="widefat" name="mmim[bill_to_email]" value="<?php echo esc_attr($bill_to_email); ?>"></label></p>
          <p><label>Address<br><textarea class="widefat" rows="4" name="mmim[bill_to_address]"><?php echo esc_textarea($bill_to_address); ?></textarea></label></p>
        </div>

        <div class="mmim-card">
          <h3>Invoice details</h3>
          <p><label>Invoice number<br><input class="widefat" name="mmim[invoice_number]" value="<?php echo esc_attr($invoice_number); ?>" placeholder="e.g. 1001"></label></p>
          <p><label>Issue date<br><input type="date" class="widefat" name="mmim[issue_date]" value="<?php echo esc_attr($issue_date); ?>"></label></p>
          <p><label>Due date<br><input type="date" class="widefat" name="mmim[due_date]" value="<?php echo esc_attr($due_date); ?>"></label></p>

          <p>
            <label>VAT rate<br>
              <select class="widefat" name="mmim[vat_rate]" id="mmim-vat-rate">
                <?php
                $rates = ['0'=>'No VAT','5'=>'5%','10'=>'10%','20'=>'20%','custom'=>'Custom'];
                foreach ($rates as $val => $label) {
                  printf('<option value="%s"%s>%s</option>', esc_attr($val), selected($vat_rate, $val, false), esc_html($label));
                }
                ?>
              </select>
            </label>
          </p>

          <p id="mmim-vat-custom-wrap" style="display:none;">
            <label>Custom VAT percent<br>
              <input class="widefat" name="mmim[vat_custom]" id="mmim-vat-custom" value="<?php echo esc_attr(self::get_meta($post->ID, '_mmim_vat_custom', '')); ?>" placeholder="e.g. 17.5">
            </label>
          </p>

          <div class="mmim-actions">
            <?php if ($pdf_url): ?>
              <a class="button" href="<?php echo esc_url($pdf_url); ?>" target="_blank" rel="noopener">View stored PDF</a>
            <?php endif; ?>
            <button type="submit" class="button button-primary" name="mmim_generate_pdf" value="1">Save and generate PDF</button>
          </div>
        </div>
      </div>

      <div class="mmim-card">
        <h3>Line items</h3>

        <table class="widefat mmim-table" id="mmim-line-items">
          <thead>
            <tr>
              <th style="width:55%;">Description</th>
              <th style="width:15%;">Qty</th>
              <th style="width:15%;">Unit price</th>
              <th style="width:15%;">Line total</th>
              <th style="width:40px;"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($line_items)) $line_items = [['desc'=>'','qty'=>'1','unit'=>'0.00']]; ?>
            <?php foreach ($line_items as $i => $row): ?>
              <tr>
                <td><input class="widefat mmim-desc" name="mmim[line_items][<?php echo esc_attr($i); ?>][desc]" value="<?php echo esc_attr($row['desc'] ?? ''); ?>"></td>
                <td><input class="widefat mmim-qty" name="mmim[line_items][<?php echo esc_attr($i); ?>][qty]" value="<?php echo esc_attr($row['qty'] ?? '1'); ?>"></td>
                <td><input class="widefat mmim-unit" name="mmim[line_items][<?php echo esc_attr($i); ?>][unit]" value="<?php echo esc_attr($row['unit'] ?? '0.00'); ?>"></td>
                <td class="mmim-line-total">0.00</td>
                <td><button type="button" class="button mmim-remove-row">X</button></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <p><button type="button" class="button" id="mmim-add-row">Add row</button></p>

        <div class="mmim-totals" data-currency="<?php echo esc_attr($settings['currency_symbol']); ?>">
          <div><span>Subtotal</span><strong id="mmim-subtotal">0.00</strong></div>
          <div><span>VAT</span><strong id="mmim-vat">0.00</strong></div>
          <div class="mmim-grand"><span>Total</span><strong id="mmim-total">0.00</strong></div>
        </div>
      </div>
    </div>
    <?php
  }

  public static function save($post_id, $post) {
    if (!current_user_can('edit_post', $post_id)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (empty($_POST['mmim_nonce']) || !wp_verify_nonce($_POST['mmim_nonce'], 'mmim_save_invoice')) return;

    $settings = MMIM_Settings::get_settings();
    $data = isset($_POST['mmim']) && is_array($_POST['mmim']) ? $_POST['mmim'] : [];

    $invoice_number = sanitize_text_field($data['invoice_number'] ?? '');

    if ($invoice_number === '' && ($settings['auto_numbering'] ?? '0') === '1') {
      $next = isset($settings['next_invoice_number']) ? intval($settings['next_invoice_number']) : 1000;
      $invoice_number = (string) $next;

      $settings['next_invoice_number'] = (string) ($next + 1);
      update_option(MMIM_Settings::OPTION_KEY, $settings);
    }

    update_post_meta($post_id, '_mmim_invoice_number', $invoice_number);
    update_post_meta($post_id, '_mmim_issue_date', sanitize_text_field($data['issue_date'] ?? ''));
    update_post_meta($post_id, '_mmim_due_date', sanitize_text_field($data['due_date'] ?? ''));

    update_post_meta($post_id, '_mmim_bill_to_name', sanitize_text_field($data['bill_to_name'] ?? ''));
    update_post_meta($post_id, '_mmim_bill_to_email', sanitize_email($data['bill_to_email'] ?? ''));
    update_post_meta($post_id, '_mmim_bill_to_address', sanitize_textarea_field($data['bill_to_address'] ?? ''));

    $vat_rate = sanitize_text_field($data['vat_rate'] ?? '20');
    $vat_custom = sanitize_text_field($data['vat_custom'] ?? '');
    update_post_meta($post_id, '_mmim_vat_rate', $vat_rate);
    update_post_meta($post_id, '_mmim_vat_custom', $vat_custom);

    $items_in = $data['line_items'] ?? [];
    $items_out = [];

    if (is_array($items_in)) {
      foreach ($items_in as $row) {
        $desc = sanitize_text_field($row['desc'] ?? '');
        $qty  = sanitize_text_field($row['qty'] ?? '1');
        $unit = sanitize_text_field($row['unit'] ?? '0.00');

        if ($desc === '' && $unit === '' ) continue;

        $items_out[] = [
          'desc' => $desc,
          'qty'  => $qty,
          'unit' => $unit,
        ];
      }
    }

    update_post_meta($post_id, '_mmim_line_items', $items_out);

    if (!empty($_POST['mmim_generate_pdf'])) {
      MMIM_PDF::generate_and_store($post_id);
    }
  }
}
