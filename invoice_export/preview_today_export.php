<?php
declare(strict_types=1);
/**
 * Preview Today's Export (read-only UI) — v2 (no icons)
 * Location: invoice_export/preview_today_export.php
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';

$backUrl = 'preview_export.php';

$backend = __DIR__ . DIRECTORY_SEPARATOR . 'back_end' . DIRECTORY_SEPARATOR . 'midway_query_only.php';
if (!is_file($backend)) {
    http_response_code(500);
    echo "Back-end script missing: " . htmlspecialchars($backend);
    exit;
}

ob_start();
include $backend;
$json = trim(ob_get_clean());
$resp = json_decode($json, true);

if (!is_array($resp) || !isset($resp['status'])) {
    http_response_code(500);
    echo "Unexpected response from query-only script.\n\nRaw output:\n" . htmlspecialchars($json);
    exit;
}
if (strtolower($resp['status']) !== 'success' || empty($resp['file'])) {
    http_response_code(500);
    echo "Export failed.\n\nDetails:\n" . htmlspecialchars($json);
    exit;
}

$csvPath = $resp['file'];
$csvExists = is_file($csvPath);

$headers = [];
$rows = [];
$totalRows = 0;
$maxDisplay = 1000;
$invoiceAmountHeader = null;
$totalAmount = 0.0;

if ($csvExists && ($fh = fopen($csvPath, 'r')) !== false) {
    $headers = fgetcsv($fh) ?: [];
    foreach ($headers as $idx => $h) {
        $name = strtolower(trim((string)$h));
        if (in_array($name, ['invoice amount', 'invoice_amount', 'total charges', 'total_charges'], true)) {
            $invoiceAmountHeader = $idx;
            break;
        }
    }
    while (($row = fgetcsv($fh)) !== false) {
        $totalRows++;
        if ($invoiceAmountHeader !== null && isset($row[$invoiceAmountHeader])) {
            $val = preg_replace('/[^0-9.\-]/', '', (string)$row[$invoiceAmountHeader]);
            if ($val !== '' && is_numeric($val)) $totalAmount += (float)$val;
        }
        if ($totalRows <= $maxDisplay) $rows[] = $row;
    }
    fclose($fh);
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Preview Today's Export</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 20px; }
    .sticky-header th { position: sticky; top: 0; background: #fff; z-index: 2; }
    .table-wrap { max-height: 60vh; overflow: auto; border: 1px solid #dee2e6; border-radius: 8px; }
    .toolbar { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .muted { color: #6c757d; }
    .badge-soft { background: #eef2ff; color: #3b5bdb; }
  </style>
</head>
<body>
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">Preview Today's Export</h1>
    <div class="toolbar">
      <a class="btn btn-outline-secondary" href="<?php echo e($backUrl); ?>">Back to Control Panel</a>
      <?php if ($csvExists): ?>
      <a class="btn btn-primary" href="file:///<?php echo e(str_replace('\\', '/', $csvPath)); ?>" target="_blank">Open CSV</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="mb-3">
    <span class="badge badge-soft">CSV Path</span>
    <code><?php echo e($csvPath); ?></code>
  </div>

  <?php if (!$csvExists): ?>
    <div class="alert alert-danger">CSV not found at the path above.</div>
  <?php else: ?>
    <div class="row mb-3 g-3">
      <div class="col-md-auto"><span class="muted">Rows (displayed):</span> <strong><?php echo number_format(count($rows)); ?></strong></div>
      <div class="col-md-auto"><span class="muted">Rows (total in CSV):</span> <strong><?php echo number_format($totalRows); ?></strong></div>
      <div class="col-md-auto"><span class="muted">Invoice Amount Sum:</span> <strong>$<?php echo number_format($totalAmount, 2); ?></strong></div>
      <?php if ($totalRows > $maxDisplay): ?>
        <div class="col-12"><span class="muted">Showing first <?php echo $maxDisplay; ?> rows for performance. Open the CSV to view all.</span></div>
      <?php endif; ?>
    </div>

    <div class="table-wrap">
      <table class="table table-sm table-striped table-hover align-middle">
        <thead class="sticky-header">
          <tr>
            <?php foreach ($headers as $h): ?>
              <th><?php echo e($h); ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <?php for ($i=0; $i < count($headers); $i++): ?>
                <td><?php echo isset($r[$i]) ? e($r[$i]) : ''; ?></td>
              <?php endfor; ?>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rows)): ?>
            <tr><td colspan="<?php echo max(1, count($headers)); ?>" class="text-center text-muted">No rows found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <div class="mt-3">
    <p class="muted mb-1">Read-only preview. This does not upload or mark USER10.</p>
  </div>
</body>
</html>
