<?php
/**
 * invexp_logger.php
 * Lightweight structured logger for Midway invoice export.
 * Writes to: C:\clients\midway\webtools\log (by default)
 */

date_default_timezone_set('America/Chicago');

class InvExpLogger {
    private $fh = null;
    private $path;
    private $t0;
    private $openOk = false;

    public function __construct($logDir, $prefix = 'invexp') {
        $this->t0 = microtime(true);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $ts = date('Ymd_His');
        $pid = function_exists('getmypid') ? @getmypid() : 0;
        $fn = $prefix . '_' . $ts . '_' . $pid . '.log';
        $this->path = rtrim($logDir, "\\/") . DIRECTORY_SEPARATOR . $fn;
        $this->fh = @fopen($this->path, 'a');
        $this->openOk = is_resource($this->fh);
        if ($this->openOk) {
            $this->line('INFO', 'logger_open', array('file'=>$this->path));
        }
    }

    public function ok() { return $this->openOk; }
    public function path() { return $this->path; }

    private function line($level, $event, $data = array()) {
        if (!$this->openOk) return;
        $elapsed = round(microtime(true) - $this->t0, 3);
        $row = array(
            'ts' => date('Y-m-d H:i:s'),
            'elapsed' => $elapsed,
            'level' => $level,
            'event' => $event,
            'data' => $data
        );
        $json = json_encode($row, JSON_UNESCAPED_SLASHES);
        @fwrite($this->fh, $json . PHP_EOL);
        @fflush($this->fh);
    }

    public function start($event, $data = array()) { $this->line('INFO', $event . '_start', $data); }
    public function step($event, $data = array()) { $this->line('INFO', $event, $data); }
    public function warn($event, $data = array()) { $this->line('WARN', $event, $data); }
    public function error($event, $data = array()) { $this->line('ERROR', $event, $data); }
    public function end($event, $ok, $data = array()) {
        $data = array_merge($data, array('ok'=>$ok ? true : false));
        $this->line('INFO', $event . '_end', $data);
    }

    public function __destruct() {
        if ($this->openOk && is_resource($this->fh)) {
            @fclose($this->fh);
        }
    }
}
