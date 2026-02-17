<?php
if (!defined('ABSPATH')) exit;

class MMIM_Settings {
  const OPTION_KEY = 'mmim_settings';

  public static function init() {
    add_action('admin_menu', [__CLASS__, 'menu']);
    add_action('admin_init', [__CLASS__, 'register_settings']);
  }

  public static function menu() {
    add_submenu_page(
      'edit.php?post_type=mm_invoice',
      'Invoice settings',
      'Settings',
      'manage_options',
      'mmim-settings',
      [__CLASS__, 'render']
    );
  }

  public static function register_settings() {
    register_setting('mmim_settings_group', self::OPTION_KEY, [
      'type' => 'array',
      'sanitize_callback' => [__CLASS__, 'sanitize'],
      'default' => [],
    ]);
  }

  public static function sanitize($input) {
    $out = [];
    $fields = [
      'business_name','business_address','business_email','business_phone',
      'bank_account_name','bank_account_number','bank_sort_code',
      'vat_number','default_terms','default_notes',
      'currency_symbol','next_invoice_number','auto_numbering'
    ];
    foreach ($fields as $f) {
      $out[$f] = isset($input[$f]) ? sanitize_text_field($input[$f]) : '';
    }
    $out['auto_numbering'] = !empty($input['auto_numbering']) ? '1' : '0';
    $out['next_invoice_number'] = is_numeric($out['next_invoice_number']) ? (string) intval($out['next_invoice_number']) : '';
    if ($out['currency_symbol'] === '') $out['currency_symbol'] = '£';
    return $out;
  }

  public static function get_settings() {
    $defaults = [
      'business_name' => '',
      'business_address' => '',
      'business_email' => '',
      'business_phone' => '',
      'bank_account_name' => '',
      'bank_account_number' => '',
      'bank_sort_code' => '',
      'vat_number' => '',
      'default_terms' => 'Payment due within 14 days.',
      'default_notes' => '',
      'currency_symbol' => '£',
      'next_invoice_number' => '1000',
      'auto_numbering' => '1',
    ];
    $saved = get_option(self::OPTION_KEY, []);
    return wp_parse_args(is_array($saved) ? $saved : [], $defaults);
  }

  public static function render() {
    if (!current_user_can('manage_options')) return;
    $s = self::get_settings();
    ?>
    <div class="wrap">
      <h1>Invoice settings</h1>
      <form method="post" action="options.php">
        <?php settings_fields('mmim_settings_group'); ?>

        <table class="form-table" role="presentation">
          <tr><th>Business name</th><td><input class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[business_name]" value="<?php echo esc_attr($s['business_name']); ?>"></td></tr>
          <tr><th>Business address</th><td><textarea class="large-text" rows="4" name="<?php echo esc_attr(self::OPTION_KEY); ?>[business_address]"><?php echo esc_textarea($s['business_address']); ?></textarea></td></tr>
          <tr><th>Email</th><td><input class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[business_email]" value="<?php echo esc_attr($s['business_email']); ?>"></td></tr>
          <tr><th>Phone</th><td><input class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[business_phone]" value="<?php echo esc_attr($s['business_phone']); ?>"></td></tr>

          <tr><th>Bank account name</th><td><input class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[bank_account_name]" value="<?php echo esc_attr($s['bank_account_name']); ?>"></td></tr>
          <tr><th>Account number</th><td><input class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[bank_account_number]" value="<?php echo esc_attr($s['bank_account_number']); ?>"></td></tr>
          <tr><th>Sort code</th><td><input class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[bank_sort_code]" value="<?php echo esc_attr($s['bank_sort_code']); ?>"></td></tr>

          <tr><th>VAT number</th><td><input class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[vat_number]" value="<?php echo esc_attr($s['vat_number']); ?>"></td></tr>
          <tr><th>Currency symbol</th><td><input style="width:80px" name="<?php echo esc_attr(self::OPTION_KEY); ?>[currency_symbol]" value="<?php echo esc_attr($s['currency_symbol']); ?>"></td></tr>

          <tr><th>Auto numbering</th><td>
            <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[auto_numbering]" value="1" <?php checked($s['auto_numbering'], '1'); ?>> Enable</label>
          </td></tr>

          <tr><th>Next invoice number</th><td><input class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[next_invoice_number]" value="<?php echo esc_attr($s['next_invoice_number']); ?>"></td></tr>

          <tr><th>Default terms</th><td><textarea class="large-text" rows="3" name="<?php echo esc_attr(self::OPTION_KEY); ?>[default_terms]"><?php echo esc_textarea($s['default_terms']); ?></textarea></td></tr>
          <tr><th>Default notes</th><td><textarea class="large-text" rows="3" name="<?php echo esc_attr(self::OPTION_KEY); ?>[default_notes]"><?php echo esc_textarea($s['default_notes']); ?></textarea></td></tr>
        </table>

        <?php submit_button('Save settings'); ?>
      </form>
    </div>
    <?php
  }
}
