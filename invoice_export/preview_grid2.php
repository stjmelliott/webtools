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
  <!-- Bootstrap 5 CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8fafc; }
    .container-narrow { max-width: 1200px; }
    .card { border-radius: 16px; }
    .badge-soft { background: #eef2ff; color: #3730a3; }
    .table-wrap { max-height: 60vh; overflow: auto; border: 1px solid #e5e7eb; border-radius: 12px; }
    thead th { position: sticky; top: 0; background: #fff; z-index: 2; }
    .small-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: .85rem; }
  </style>
</head>
<body>
  <div class="container container-narrow py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h4 mb-0">Midway Export — Preview Grid</h1>
      <span class="badge badge-soft rounded-pill px-3">Standalone</span>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-primary" href="?refresh=1">🔄 Refresh CSV</a>
          <a class="btn btn-secondary" href="?download=1">⬇️ Download CSV</a>
          <a class="btn btn-outline-secondary" href="export_all_in_one.php">⬅️ Back to Control Panel</a>
          <button class="btn btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#rawJson">🐞 Toggle Raw JSON</button>
        </div>

        <div class="mt-3 row g-3">
          <div class="col-md-6 col-lg-7">
            <div class="small text-muted">CSV Path:</div>
            <div class="small-mono"><?php echo h($csv_path ?: '—'); ?></div>
          </div>
          <div class="col-md-6 col-lg-5">
            <div class="small text-muted">Meta:</div>
            <div class="small-mono">
              Mode=<?php echo h($mode); ?><?php if ($deleted_prev) echo " (deleted prev)"; ?> ·
              Rows=<?php echo h($displayed_rows . '/' . $total_rows); ?><?php if ($truncated) echo " (truncated)"; ?> ·
              <?php if ($sum !== null) { echo 'Sum=$' . number_format((float)$sum, 2); } ?> ·
              PHP=<?php echo h($php_used ?: 'n/a'); ?>
            </div>
          </div>
        </div>

        <?php if(!$ok): ?>
          <div class="alert alert-danger mt-3">
            <div class="fw-bold">Preview failed</div>
            <div class="small-mono"><?php echo h(isset($data['error']) ? $data['error'] : 'Unknown error'); ?></div>
          </div>
        <?php endif; ?>

        <div id="rawJson" class="collapse mt-3">
          <pre class="small-mono bg-light p-2 border rounded" style="max-height: 40vh; overflow:auto;"><?php echo h(json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)); ?></pre>
        </div>
      </div>
    </div>

    <?php if ($ok): ?>
      <div class="card">
        <div class="card-body">
          <?php if (empty($headers)): ?>
            <div class="alert alert-warning mb-0">CSV has no headers.</div>
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
