<?php
/**
 * Midway Export — All-in-One Console (v3c Preview Fix + Visible Diagnostics)
 * Place as: C:\clients\midway\webtools\invoice_export\export_all_in_one.php
 */

date_default_timezone_set('America/Chicago');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');
if ($DEBUG && !defined('NET_SSH2_LOGGING')) { define('NET_SSH2_LOGGING', 3); }

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';

function invexp_json_out($arr) { header('Content-Type: application/json; charset=utf-8'); echo json_encode($arr); exit; }
function invexp_capture_include($phpFile) {
  if (!is_file($phpFile)) return array('ok'=>false, 'output'=>"File not found: $phpFile", 'rc'=>-1);
  ob_start(); include $phpFile; $out = ob_get_clean();
  $rc = null;
  if (preg_match('/Return code:\s*(\d+)/i', $out, $m)) $rc = (int)$m[1];
  elseif (preg_match('/Return code:\s*0\b/i', $out)) $rc = 0;
  $ok = ($rc !== null) ? ($rc === 0) : (stripos($out, 'ERROR:') === false);
  return array('ok'=>$ok, 'output'=>$out, 'rc'=>$rc);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'ui';

if ($action === 'preview' || $action === 'preview_force') {
  $wrapper = __DIR__ . DIRECTORY_SEPARATOR . 'back_end' . DIRECTORY_SEPARATOR . 'midway_query_only_wrapper.php';
  $meta = array(
    'wrapper' => $wrapper,
    'wrapper_exists' => is_file($wrapper) ? true : false,
    'version' => 'v3c'
  );
  if (!is_file($wrapper)) invexp_json_out(array('ok'=>false,'error'=>"Wrapper missing", 'meta'=>$meta));

  // Force flag for the wrapper
  $_INVEXP_FORCE = ($action === 'preview_force');

  ob_start(); include $wrapper; $raw = trim(ob_get_clean());
  $j = json_decode($raw, true);
  if (!is_array($j)) {
    invexp_json_out(array('ok'=>false,'error'=>'Unexpected response from query-only backend (first pass)','raw'=>$raw,'meta'=>$meta));
  }
  invexp_json_out(array('ok'=>true,'data'=>$j,'debug'=>$DEBUG,'meta'=>$meta));
}

if ($action === 'full_export') {
  $file = __DIR__ . DIRECTORY_SEPARATOR . 'back_end' . DIRECTORY_SEPARATOR . 'midway_full_export.php';
  $res = invexp_capture_include($file); $res['debug']=$DEBUG; invexp_json_out($res);
}

if ($action === 'upload_sit') {
  $file = __DIR__ . DIRECTORY_SEPARATOR . 'back_end' . DIRECTORY_SEPARATOR . 'midway_sftp_upload_sandbox.php';
  $res = invexp_capture_include($file); $res['debug']=$DEBUG; invexp_json_out($res);
}

if ($action === 'list_sit') {
  $creds = isset($INVEXP_CONFIG['sftp']['test']) ? $INVEXP_CONFIG['sftp']['test'] : array();
  $host = isset($creds['host']) ? $creds['host'] : '';
  $port = isset($creds['port']) ? (int)$creds['port'] : 22;
  $user = isset($creds['user']) ? $creds['user'] : '';

  if (!function_exists('invexp_phpseclib_available') || !invexp_phpseclib_available())
    invexp_json_out(array('ok'=>false,'error'=>'phpseclib not available'));
  if (!class_exists('phpseclib\\Net\\SFTP', true) || !class_exists('phpseclib\\Crypt\\RSA', true))
    invexp_json_out(array('ok'=>false,'error'=>'SFTP/RSA classes unavailable'));

  $keyPath = isset($creds['key_path']) ? $creds['key_path'] : '';
  $keyPass = isset($creds['key_passphrase']) ? $creds['key_passphrase'] : null;
  $password = isset($creds['password']) ? $creds['password'] : null;

  $key = null;
  if ($keyPath && is_file($keyPath)) {
    $pem = @file_get_contents($keyPath);
    if ($pem !== false) {
      $key = new \phpseclib\Crypt\RSA();
      if ($keyPass) $key->setPassword($keyPass);
      $key->loadKey($pem);
    }
  }

  $sftp = new \phpseclib\Net\SFTP($host, $port, 10);
  $ok = false;
  if ($key) $ok = $sftp->login($user, $key);
  if (!$ok && $password) $ok = $sftp->login($user, $password);
  if (!$ok) invexp_json_out(array('ok'=>false,'error'=>'Auth failed'));
  $list = $sftp->nlist('/incoming/generic_ff');
  invexp_json_out(array('ok'=>($list!==false),'entries'=>$list ? $list : array()));
}

?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Midway Export — All-in-One Console <small class="text-muted">v3c</small></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 20px; }
    .grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
    @media(min-width: 1100px) { .grid { grid-template-columns: 1.2fr .8fr; } }
    .card { border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
    .sticky { position: sticky; top: 0; background: #fff; z-index: 2; }
    .table-wrap { max-height: 52vh; overflow: auto; border: 1px solid #dee2e6; border-radius: 8px; }
    .muted { color: #6c757d; }
    pre.mono { background: #f6f8fa; padding: 12px; border-radius: 6px; white-space: pre-wrap; }
    .toolbar { display:flex; gap: 8px; align-items:center; flex-wrap: wrap; }
    .badge-soft { background: #eef2ff; color: #3b5bdb; }
    .kv { display:grid; grid-template-columns: 160px 1fr; gap: 8px; }
    .kv .k { color:#6c757d; }
    details summary { cursor: pointer; }
  </style>
</head>
<body>
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">Midway Export — All-in-One Console <span class="badge bg-light text-dark">v3c</span></h1>
    <div class="toolbar">
      <a class="btn btn-outline-secondary" href="preview_export.php">Back to Control Panel</a>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="debugToggle">
        <label class="form-check-label" for="debugToggle">Debug mode</label>
      </div>
    </div>
  </div>

  <div class="grid">
    <div class="card p-3">
      <h2 class="h6">Preview Today's Export</h2>
      <div class="d-flex gap-2 mb-2">
        <button class="btn btn-secondary" id="btnPreview">Preview</button>
        <button class="btn btn-outline-secondary" id="btnPreviewForce">Refresh Preview</button>
        <span class="badge badge-soft" id="csvPathBadge" style="display:none;"></span>
      </div>

      <div id="previewAlert"></div>

      <div class="row g-3 my-1" id="previewSummary" style="display:none;">
        <div class="col-lg-4">
          <div class="border rounded p-2">
            <div class="kv">
              <div class="k">CSV path</div><div id="csvPathVal" class="text-truncate"></div>
              <div class="k">Last modified</div><div id="csvMtimeVal"></div>
              <div class="k">Rows (displayed)</div><div id="rowsDispVal"></div>
              <div class="k">Rows (total)</div><div id="rowsTotVal"></div>
              <div class="k">Invoice Sum</div><div id="sumVal"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="table-wrap">
            <table class="table table-sm table-striped table-hover align-middle">
              <thead class="sticky"><tr id="theadRow"></tr></thead>
              <tbody id="tbodyRows"><tr><td class="muted">No data yet.</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>

      <details class="mt-3" id="rawBox" style="display:none;">
        <summary>Show raw backend output</summary>
        <pre class="mono" id="rawOut"></pre>
      </details>
    </div>

    <div class="card p-3">
      <h2 class="h6">Actions</h2>
      <div class="d-flex gap-2 mb-2">
        <button class="btn btn-primary" id="btnFullExport">Run Full Export</button>
        <button class="btn btn-outline-primary" id="btnUploadSIT">Upload to Sandbox (SIT)</button>
        <button class="btn btn-outline-secondary" id="btnListSIT">List SIT Inbox</button>
      </div>
      <div class="muted mb-2">Status:</div>
      <pre id="status" class="mono">(nothing yet)</pre>
    </div>
  </div>

<script>
const qs = (s)=>document.querySelector(s);
const statusBox = qs('#status');
const debugToggle = qs('#debugToggle');
const alertBox = qs('#previewAlert');
const summaryBox = qs('#previewSummary');
const bBadge = qs('#csvPathBadge');
const rawBox = qs('#rawBox');
const rawOut = qs('#rawOut');

function setStatus(text){ statusBox.textContent = text; }
function fmtMoney(n){ return '$' + Number(n||0).toFixed(2); }

async function call(action) {
  const debug = debugToggle.checked ? '&debug=1' : '';
  const res = await fetch(`export_all_in_one.php?action=${action}${debug}`, { method: 'GET', cache: 'no-store' });
  const txt = await res.text();
  try { return JSON.parse(txt); } catch (e) { return { ok:false, error:'Non-JSON', raw:txt }; }
}

function labelFrom(j){
  if (typeof j.rc === 'number') {
    if (j.rc === 0) return 'OK';
    const out = (j.output||'') + (j.raw||'') + (j.error||'');
    if (/Missing SFTP host\/user/i.test(out)) return 'NOT CONFIGURED';
    return 'ERROR';
  }
  const out = (j.output||'') + (j.raw||'') + (j.error||'');
  if (out.indexOf('Return code: 0') !== -1) return 'OK';
  if (/Missing SFTP host\/user/i.test(out)) return 'NOT CONFIGURED';
  return j.ok ? 'OK' : 'ERROR';
}

function showPreviewUI(meta, d){
  // Alert
  let html = '';
  if (d.meta && d.meta.mode === 'refresh') {
    if (d.meta.deleted_previous_csv) {
      html = `<div class="alert alert-success py-2"><strong>Refreshed:</strong> Deleted previous CSV and generated a new one.</div>`;
    } else {
      html = `<div class="alert alert-warning py-2"><strong>Refreshed:</strong> No prior CSV to delete; generated or used current file.</div>`;
    }
  } else {
    html = `<div class="alert alert-info py-2"><strong>Preview:</strong> Using today's CSV.</div>`;
  }
  alertBox.innerHTML = html;

  // Summary + table
  summaryBox.style.display = 'block';
  bBadge.style.display = 'inline-block';
  bBadge.textContent = d.csv_path || '(no path)';
  qs('#csvPathVal').textContent = d.csv_path || '';
  qs('#csvMtimeVal').textContent = d.csv_mtime || '';
  qs('#rowsDispVal').textContent = d.displayed_rows || 0;
  qs('#rowsTotVal').textContent = d.total_rows || 0;
  qs('#sumVal').textContent = fmtMoney(d.invoice_amount_sum || 0);

  const thead = qs('#theadRow'); thead.innerHTML='';
  (d.headers||[]).forEach(h=>{ const th=document.createElement('th'); th.textContent=h; thead.appendChild(th); });
  const tbody = qs('#tbodyRows'); tbody.innerHTML='';
  if((d.rows||[]).length===0){
    const tr=document.createElement('tr'); const td=document.createElement('td');
    td.className='muted'; td.textContent='No rows found.';
    td.colSpan=(d.headers||[]).length||1; tr.appendChild(td); tbody.appendChild(tr);
  } else {
    d.rows.forEach(r=>{
      const tr=document.createElement('tr');
      for(let i=0;i<d.headers.length;i++){
        const td=document.createElement('td'); td.textContent = (r[i]!==undefined ? r[i] : ''); tr.appendChild(td);
      }
      tbody.appendChild(tr);
    });
  }
}

async function doPreview(kind){
  setStatus(kind==='force' ? 'Refreshing preview...' : 'Running preview...');
  const j = await call(kind==='force' ? 'preview_force' : 'preview');
  if(!j.ok){
    setStatus('Preview failed.');
    const where = (j.meta && j.meta.wrapper) ? `<div>Wrapper: <code>${j.meta.wrapper}</code> (exists: ${j.meta.wrapper_exists ? 'yes':'no'})</div>` : '';
    alertBox.innerHTML = `<div class="alert alert-danger py-2">
      <strong>Preview failed:</strong> ${j.error||'Unknown error'}${where}
    </div>`;
    rawBox.style.display = 'block';
    rawBox.setAttribute('open','');
    rawOut.textContent = (j.raw||'(no raw output)');
    return;
  }
  rawBox.style.display = 'none'; rawOut.textContent = '';
  const d = j.data;
  showPreviewUI(d.meta, d);
  setStatus(kind==='force' ? 'Preview refreshed.' : 'Preview complete.');
}

qs('#btnPreview').addEventListener('click', ()=>doPreview('normal'));
qs('#btnPreviewForce').addEventListener('click', ()=>doPreview('force'));

qs('#btnFullExport').addEventListener('click', async ()=>{
  setStatus('Running full export...');
  const j = await call('full_export');
  const label = labelFrom(j);
  setStatus(label + (typeof j.rc === 'number'?` (rc=${j.rc})`:'') + '\n\n' + (j.output||j.raw||''));
});

qs('#btnUploadSIT').addEventListener('click', async ()=>{
  setStatus('Uploading to SIT...');
  const j = await call('upload_sit');
  const label = labelFrom(j);
  setStatus(label + (typeof j.rc === 'number'?` (rc=${j.rc})`:'') + '\n\n' + (j.output||j.raw||''));
});

qs('#btnListSIT').addEventListener('click', async ()=>{
  setStatus('Listing SIT inbox...');
  const j = await call('list_sit');
  if(!j.ok){ setStatus('List failed: ' + (j.error||'unknown')); return; }
  setStatus('Inbox entries:\n' + (j.entries||[]).join('\n'));
});
</script>
</body>
</html>
