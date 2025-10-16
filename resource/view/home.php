<?php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

?>

<?php
$end = microtime(true);
$runtime = $end - CRAFT_RUN;
?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Framework — Status</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: #ffffff;
      color: #222;
    }
    .wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }
    .card {
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 16px;
      max-width: 760px;
      width: 100%;
      background: #fff;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    h1 {
      font-size: 20px;
      margin-top: 0;
    }
    .grid {
      display: -ms-grid;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 10px;
      margin-top: 16px;
    }
    .item {
      border: 1px solid #e5e5e5;
      border-radius: 8px;
      padding: 10px;
      background: #fafafa;
    }
    .k { font-size: 12px; color: #666; }
    .v { font-weight: bold; font-size: 14px; word-break: break-all; }
    .footer {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      margin-top: 16px;
      font-size: 12px;
      color: #555;
    }
    button {
      border: 1px solid #aaa;
      background: #f5f5f5;
      border-radius: 6px;
      cursor: pointer;
      padding: 4px 10px;
    }
    button:hover {
      background: #e0e0e0;
    }
    @media (max-width: 520px) {
      .grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>CraftPHP mini-Framework: <span id="install-status">Successfully</span></h1>
      <p>Application information & runtime status</p>
      <div class="grid">
        <div class="item"><div class="k">App name</div><div id="app-name" class="v"><?=env('APP_NAME')?></div></div>
        <div class="item"><div class="k">Environment</div><div id="app-env" class="v"><?=env('APP_ENVIRONMENT')?></div></div>
        <div class="item"><div class="k">App timezone</div><div id="app-timezone" class="v"><?=env('APP_TIMEZONE')?></div></div>
        <div class="item"><div class="k">Debug</div><div id="app-debug" class="v"><?=env('APP_DEBUG') ? 'true':'false' ?></div></div>
        <div class="item"><div class="k">Run time</div><div id="craft-run" class="v"><?=round($runtime, 4)?> secs.</div></div>
        <div class="item"><div class="k">Memory usage</div><div id="mem-used" class="v"><?=formatBytes(memory_get_usage())?></div></div>
        <div class="item"><div class="k">Framework version</div><div id="fw-version" class="v"><?=\Craft\Application\App::version?></div></div>
      </div>
      <div class="footer">
        <div>Status: <span id="service-status">Running</span></div>
        <div>Updated at: <span id="updated-at"><?=date('Y-m-d H:i:s')?></span></div>
      </div>
    </div>
  </div>
</body>
</html>
