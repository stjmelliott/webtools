<?php
/**
 * preview_grid.php  — Standalone Bootstrap preview page
 * Location: C:\clients\midway\webtools\invoice_export\preview_grid.php
 *
 * What it does
 * - Embeds back_end/midway_query_only_wrapper.php (no HTTP calls)
 * - Shows a Bootstrap table of the CSV (headers + rows) returned by the wrapper
 * - Buttons: Refresh CSV (force rebuild), Download CSV, Back to Control Panel
 *
 * Requires:
 *   - back_end\midway_query_only_wrapper.php (v3g1+)
 *   - config\invoice_export_config.php with $INVEXP_CONFIG['php']['cli_path'] set
 */

@ini_set('display_errors', 0);
date_default_timezone_set('America/Chicago');

$baseDir = __DIR__;
$wrapper = $baseDir . DIRECTORY_SEPARATOR . 'back_end' . DIRECTORY_SEPARATOR . 'midway_query_only_wrapper.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// --- Helper: run wrapper embedded and return array ---------------------------
function run_wrapper($force=false, $wrapper_path='') {
  if (!is_file($wrapper_path)) {
    return array('ok'=>false,'error'=>'Wrapper not found','wrapper'=>$wrapper_path);
  }
  if (!defined('INVEXP_EMBED')) { define('INVEXP_EMBED', true); }
  if ($force) { $GLOBALS['_INVEXP_FORCE'] = true; }
  include $wrapper_path;
  $res = isset($GLOBALS['INVEXP_WRAPPER_RESULT']) ? $GLOBALS['INVEXP_WRAPPER_RESULT'] : null;
  unset($GLOBALS['INVEXP_WRAPPER_RESULT'], $GLOBALS['_INVEXP_FORCE']);
  return is_array($res) ? $res : array('ok'=>false,'error'=>'Wrapper returned no data');
}

// --- Download route ----------------------------------------------------------
if (isset($_GET['download'])) {
  $r = run_wrapper(false, $wrapper);
  if (is_array($r) && !empty($r['ok']) && !empty($r['csv_path']) && is_file($r['csv_path'])) {
    $csv = $r['csv_path'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . basename($csv) . '"');
    header('Content-Length: ' . filesize($csv));
    readfile($csv);
    exit;
  } else {
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(404);
    echo "CSV not available for download.\n";
    if (is_array($r)) {
      echo json_encode($r, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }
    exit;
  }
}

// --- Normal render -----------------------------------------------------------
$force = (isset($_GET['refresh']) && $_GET['refresh'] === '1');
$data = run_wrapper($force, $wrapper);
$ok = is_array($data) && !empty($data['ok']);
$headers = $ok && isset($data['headers']) && is_array($data['headers']) ? $data['headers'] : array();
$rows = $ok && isset($data['rows']) && is_array($data['rows']) ? $data['rows'] : array();
$csv_path = $ok ? (isset($data['csv_path']) ? $data['csv_path'] : '') : '';
$csv_mtime = $ok ? (isset($data['csv_mtime']) ? $data['csv_mtime'] : '') : '';
$total_rows = $ok ? (isset($data['total_rows']) ? (int)$data['total_rows'] : 0) : 0;
$displayed_rows = $ok ? (isset($data['displayed_rows']) ? (int)$data['displayed_rows'] : 0) : 0;
$sum = $ok ? (isset($data['invoice_amount_sum']) ? $data['invoice_amount_sum'] : null) : null;
$truncated = $ok ? (!empty($data['truncated'])) : false;
$mode = $ok && isset($data['meta']['mode']) ? $data['meta']['mode'] : ($force?'refresh':'preview');
$php_used = $ok && isset($data['meta']['php_used']) ? $data['meta']['php_used'] : '';
$deleted_prev = $ok && isset($data['meta']['deleted_previous_csv']) ? (bool)$data['meta']['deleted_previous_csv'] : false;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Midway Export — Preview Grid</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --brand: #28a745;
      --brand-dark: #1e7e34;
      --bg-light: #f8fafc;
      --card-border: #e0e0e0;
      --text-primary: #1a1a1a;
      --text-secondary: #546e7a;
    }
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: linear-gradient(to bottom, #ffffff, #e6f9e6);
      min-height: 100vh;
      color: var(--text-primary);
    }
    .navbar {
      background: var(--brand) !important;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      padding: 1rem;
    }
    .navbar-brand {
      font-weight: 600;
      font-size: 1.5rem;
      color: #fff;
    }
    .container {
      max-width: 1200px;
      padding: 2rem 1rem;
    }
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      overflow: hidden;
    }
    .card-header {
      background: #e6f4ea;
      border-bottom: none;
      padding: 1.25rem 1.5rem;
      font-weight: 600;
      color: var(--brand-dark);
    }
    .card-body {
      padding: 1.5rem;
    }
    .btn-primary {
      background: var(--brand);
      border: none;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: background 0.2s ease, transform 0.1s ease;
    }
    .btn-primary:hover {
      background: var(--brand-dark);
      transform: translateY(-1px);
    }
    .btn-secondary {
      background: #6c757d;
      border: none;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: background 0.2s ease, transform 0.1s ease;
    }
    .btn-secondary:hover {
      background: #5a6268;
      transform: translateY(-1px);
    }
    .btn-outline-primary {
      border-color: var(--brand);
      color: var(--brand);
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: background 0.2s ease, transform 0.1s ease;
    }
    .btn-outline-primary:hover {
      background: var(--brand);
      color: #fff;
      transform: translateY(-1px);
    }
    .btn-outline-dark {
      border-color: #343a40;
      color: #343a40;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: background 0.2s ease, transform 0.1s ease;
    }
    .btn-outline-dark:hover {
      background: #343a40;
      color: #fff;
      transform: translateY(-1px);
    }
    .btn-group {
      display: flex;
      gap: 1rem;
    }
    .btn-group .btn {
      margin-right: 0;
    }
    .alert {
      border-radius: 8px;
      padding: 1rem;
      font-size: 0.95rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .alert-danger {
      background: #ffebee;
      color: #c62828;
    }
    .alert-warning {
      background: #fff3e0;
      color: #e65100;
    }
    .icon {
      width: 1.25rem;
      text-align: center;
      margin-right: 0.75rem;
    }
    .small-mono {
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      font-size: 0.85rem;
      color: var(--text-secondary);
    }
    .table-wrap {
      max-height: 60vh;
      overflow: auto;
      border: 1px solid var(--card-border);
      border-radius: 8px;
    }
    .table {
      margin-bottom: 0;
    }
    thead th {
      position: sticky;
      top: 0;
      background: #fff;
      z-index: 2;
      font-weight: 600;
      color: var(--text-primary);
      border-bottom: 2px solid var(--card-border);
    }
    tbody td {
      font-size: 0.9rem;
      color: var(--text-primary);
    }
    .text-muted {
      color: var(--text-secondary) !important;
    }
    .border {
      border: 1px solid var(--card-border) !important;
    }
    .rounded {
      border-radius: 8px !important;
    }
    .fw-semibold {
      font-weight: 600 !important;
    }
    @media (max-width: 768px) {
      .btn-group {
        flex-direction: column;
        gap: 0.75rem;
      }
      .btn-group .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-dark mb-4">
    <div class="container">
      <span class="navbar-brand"><i class="fa-solid fa-table me-2"></i>Midway Export — Preview Grid</span>
    </div>
  </nav>

  <div class="container mb-5">
    <?php if(!$ok): ?>
      <div class="alert alert-danger">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <strong>Preview failed</strong>
        <div class="small-mono"><?php echo h(isset($data['error']) ? $data['error'] : 'Unknown error'); ?></div>
      </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
      <div class="card-header d-flex align-items-center">
        <i class="fa-solid fa-info-circle icon"></i><strong>Export Information</strong>
      </div>
      <div class="card-body">
        <div class="btn-group mb-3">
          <a class="btn btn-primary" href="?refresh=1"><i class="fa-solid fa-rotate me-1"></i>Refresh CSV</a>
          <a class="btn btn-secondary" href="?download=1"><i class="fa-solid fa-download me-1"></i>Download CSV</a>
          <a class="btn btn-outline-primary" href="export_all_in_one.php"><i class="fa-solid fa-arrow-left me-1"></i>Back to Control Panel</a>
          <button class="btn btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#rawJson"><i class="fa-solid fa-bug me-1"></i>Toggle Raw JSON</button>
        </div>

        <div class="row g-3">
          <div class="col-md-6 col-lg-7">
            <div class="text-muted small">CSV Path</div>
            <div class="small-mono"><?php echo h($csv_path ?: '—'); ?></div>
          </div>
          <div class="col-md-6 col-lg-5">
            <div class="text-muted small">Meta</div>
            <div class="small-mono">
              Mode=<?php echo h($mode); ?><?php if ($deleted_prev) echo " (deleted prev)"; ?> ·
              Rows=<?php echo h($displayed_rows . '/' . $total_rows); ?><?php if ($truncated) echo " (truncated)"; ?> ·
              <?php if ($sum !== null) { echo 'Sum=$' . number_format((float)$sum, 2); } ?> ·
              PHP=<?php echo h($php_used ?: 'n/a'); ?>
            </div>
          </div>
        </div>

        <div id="rawJson" class="collapse mt-3">
          <pre class="small-mono bg-white p-3 border rounded" style="max-height: 40vh; overflow:auto;"><?php echo h(json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)); ?></pre>
        </div>
      </div>
    </div>

    <?php if ($ok): ?>
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
          <i class="fa-solid fa-table-list icon"></i><strong>Data Preview</strong>
        </div>
        <div class="card-body">
          <?php if (empty($headers)): ?>
            <div class="alert alert-warning mb-0">
              <i class="fa-solid fa-triangle-exclamation me-2"></i>CSV has no headers.
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table table-sm table-striped table-bordered align-middle">
                <thead>
                  <tr>
                    <?php foreach ($headers as $hcol): ?>
                      <th class="text-nowrap"><?php echo h($hcol); ?></th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($rows)): ?>
                    <tr><td colspan="<?php echo count($headers); ?>" class="text-center text-muted">No data rows.</td></tr>
                  <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                      <tr>
                        <?php for ($i=0; $i<count($headers); $i++): ?>
                          <td><?php echo isset($r[$i]) ? h($r[$i]) : ''; ?></td>
                        <?php endfor; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>