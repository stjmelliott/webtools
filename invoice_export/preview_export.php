<?php
declare(strict_types=1);
/**
 * Midway Daily Export — Control Panel (cleaned)
 * Location: invoice_export/preview_export.php
 * - Adds "Preview Today's Export" as a separate page link
 * - Removes the "Output will appear here..." box
 * - Normalizes button casing for end users
 * NOTE: This page assumes existing back-end endpoints remain unchanged.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Midway Daily Export</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 20px; }
    .btn-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
    #status { white-space: pre-wrap; font-family: ui-monospace, Menlo, Consolas, monospace; background: #f6f8fa; padding: 10px; border-radius: 6px; border: 1px solid #e1e4e8; display:none; }
  </style>
</head>
<body>
  <h1 class="h4 mb-3">Midway Daily Export</h1>

  <div class="btn-row">
    <a class="btn btn-secondary" href="preview_today_export.php">Preview Today's Export</a>
    <button class="btn btn-primary" id="btnFullExport" type="button">Run Full Export</button>
    <button class="btn btn-outline-primary" id="btnSandbox" type="button">Export to Sandbox (SIT)</button>
    <button class="btn btn-danger" id="btnProd" type="button">Send Invoice to Sherwin-Williams</button>
  </div>

  <div id="status"></div>

<script>
function show(msg) {
  const s = document.getElementById('status');
  s.style.display = 'block';
  s.textContent = msg;
}

// Wire buttons to existing back-end endpoints (no output box needed; just status)
document.getElementById('btnFullExport').addEventListener('click', async () => {
  show('Running full export...');
  try {
    const r = await fetch('back_end/midway_full_export.php', {cache: 'no-store'});
    const t = await r.text();
    show('Full export completed.\n\n' + t);
  } catch (e) { show('Error: ' + e); }
});

document.getElementById('btnSandbox').addEventListener('click', async () => {
  show('Uploading to SIT...');
  try {
    const r = await fetch('back_end/midway_sftp_upload_sandbox.php', {cache:'no-store'});
    const t = await r.text();
    show('Sandbox upload done.\n\n' + t);
  } catch (e) { show('Error: ' + e); }
});

document.getElementById('btnProd').addEventListener('click', async () => {
  if (!confirm('Are you sure you want to send the invoice to Sherwin-Williams (PROD)?')) return;
  show('Uploading to PROD...');
  try {
    const r = await fetch('back_end/midway_sftp_upload.php', {cache:'no-store'});
    const t = await r.text();
    show('PROD upload done.\n\n' + t);
  } catch (e) { show('Error: ' + e); }
});
</script>
</body>
</html>
