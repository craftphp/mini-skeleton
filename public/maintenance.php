<?php 
if(!function_exists('env') || env('MAINTENANCE_MODE') !== 'true'){
    exit;
}
?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Bảo trì hệ thống</title>
  <meta name="color-scheme" content="light dark" />
  <style>
    /* ====== Thiết lập cơ bản, chỉ HTML + CSS, không JS ====== */
    :root{
      /* Màu sắc cơ bản */
      --bg: #0b0c0f;
      --panel: #111317;
      --text: #e7e9ee;
      --muted: #a8afbd;
      --accent: #7aa2ff;
      --border: color-mix(in oklab, var(--text) 12%, transparent);
      /* Kích thước co giãn theo viewport, nhưng có giới hạn */
      --radius: 24px;
      --space: clamp(12px, 2vw, 28px);
      --space-lg: clamp(16px, 3.2vw, 40px);
      --w-card: min(92vw, 720px);
    }

    @media (prefers-color-scheme: light){
      :root{
        --bg: #f6f7fb;
        --panel: #ffffff;
        --text: #0f1220;
        --muted: #55607a;
        --accent: #2257ff;
        --border: color-mix(in oklab, var(--text) 12%, transparent);
      }
    }

    *{ box-sizing: border-box; }
    html, body{ height: 100%; }
    body{
      margin: 0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      color: var(--text);
      background: radial-gradient(1200px 800px at 15% 10%, color-mix(in oklab, var(--accent) 16%, transparent), transparent 55%),
                  radial-gradient(1000px 600px at 85% 15%, color-mix(in oklab, var(--accent) 10%, transparent), transparent 55%),
                  var(--bg);
      display: grid;
      place-items: center;
      padding: clamp(6px, 2vw, 24px);
    }

    .card{
      width: var(--w-card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: linear-gradient(180deg, color-mix(in oklab, var(--panel) 92%, transparent), var(--panel));
      box-shadow: 0 20px 60px color-mix(in oklab, #000 16%, transparent);
      overflow: clip;
    }

    .content{
      padding: var(--space-lg);
      display: grid;
      gap: var(--space);
      text-wrap: pretty;
    }

    .badge{
      width: fit-content;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 10px;
      font-size: clamp(11px, 1.2vw, 13px);
      letter-spacing: .4px;
      text-transform: uppercase;
      border-radius: 999px;
      border: 1px dashed var(--border);
      background: color-mix(in oklab, var(--accent) 12%, transparent);
    }

    .icon{ width: 1em; height: 1em; display: inline-block; }

    h1{
      margin: 0;
      font-size: clamp(20px, 4.2vw, 36px);
      line-height: 1.15;
      letter-spacing: -0.02em;
    }

    p{ 
      margin: 0; 
      font-size: clamp(13px, 1.8vw, 16px); 
      color: var(--muted);
      line-height: 1.6;
    }

    .details{ 
      display: grid; 
      gap: 4px; 
      border-top: 1px solid var(--border);
      padding: var(--space);
      font-size: clamp(12px, 1.6vw, 14px);
      color: var(--muted);
    }

    .time{ display: inline-grid; gap: 3px; }

    .footer{
      border-top: 1px solid var(--border);
      padding: var(--space);
      display: flex;
      flex-wrap: wrap;
      gap: 8px 14px;
      align-items: center;
      justify-content: space-between;
      font-size: clamp(11px, 1.5vw, 13px);
      color: var(--muted);
    }

    .brand{ font-weight: 600; color: var(--text); }

    /* Đảm bảo hiển thị tốt từ cực nhỏ (128px) đến màn hình 4K */
    @media (max-width: 220px){
      .content{ padding: 12px; }
      .footer{ padding: 10px; }
      .badge{ padding: 4px 8px; }
    }

    @media (min-width: 1921px){
      :root{ --w-card: min(60vw, 1200px); }
    }
  </style>
</head>
<body>
  <main class="card" role="main" aria-labelledby="title">
    <div class="content">
      <span class="badge" aria-hidden="true">
        <!-- Biểu tượng cờ lê dạng SVG tối giản -->
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M22 2a7 7 0 0 1-9.9 9.9L7 17l-3 3 3-3 4.1-4.1A7 7 0 0 1 22 2z"/>
          <circle cx="7" cy="17" r="1.6"/>
        </svg>
        Bảo trì
      </span>

      <h1 id="title">Chúng tôi đang bảo trì hệ thống</h1>
      <p>Một số tính năng sẽ tạm thời không khả dụng. Vui lòng quay lại sau ít phút nữa. Cảm ơn bạn đã thông cảm!</p>
    </div>

    <footer class="footer">
      <span class="brand" aria-label="Tên hệ thống">YourService</span>
      <span>Liên hệ hỗ trợ: support@example.com</span>
    </footer>
  </main>
</body>
</html>
