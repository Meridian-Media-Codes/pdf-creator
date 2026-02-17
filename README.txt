MM Invoice Maker (v1.0.0)

Install:
1) Upload the plugin folder or zip in WordPress -> Plugins -> Add New -> Upload.
2) Activate.

PDF dependency:
This plugin expects Dompdf to be installed via Composer inside the plugin folder.
Run:
  composer install --no-dev

If /vendor/ is missing, PDF generation will be skipped (no fatal error).

Usage:
WP Admin -> Invoices -> Add New
- Fill in bill-to details, add line items, choose VAT rate.
- Click "Save and generate PDF" to store a PDF in /wp-content/uploads/mm-invoices/
