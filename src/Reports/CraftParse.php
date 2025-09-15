<?php
namespace Craft\Reports;

/**
 * #### ParseReporting class for handling PHP parse analysis and reporting.
 *
 * This class captures parse errors, warnings, and other related issues,
 * providing a structured way to log and display them.
 */
class CraftParse
{
    private $saveLog;
    private $logFile;

    /**
     * ParseReporting is a class that handles PHP parse analysis and reporting.
     * @param mixed $saveLog Save log or not.
     * @param mixed $logFile Set the log file name.
     */
    public function __construct($saveLog = false, $logFile = 'parse.log')
    {
        $this->saveLog = $saveLog;
        $this->logFile = $logFile;

        // Register shutdown function to catch parse errors
        register_shutdown_function([$this, 'handleShutdown']);

        // Set error handler for parse-related errors
        set_error_handler([$this, 'handleParseError'], E_PARSE | E_COMPILE_ERROR | E_COMPILE_WARNING);
    }

    /**
     * Sign the ParseReporting class to handle parse errors.
     * @param bool $saveLog
     * @param string $logFile
     * @return self
     */
    public static function sign($saveLog = false, $logFile = 'parse.log')
    {
        return new self($saveLog, $logFile);
    }

    /**
     * Handle shutdown to catch fatal parse errors
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_PARSE, E_COMPILE_ERROR, E_COMPILE_WARNING, E_ERROR])) {
            $this->handleParse(
                $error['message'],
                $error['file'],
                $error['line'],
                $error['type'] === E_PARSE ? 'error' : 'warning'
            );
            exit;
        }
    }

    /**
     * Handle parse-related errors
     */
    public function handleParseError($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_PARSE:
            case E_COMPILE_ERROR:
                $type = 'error';
                break;
            case E_COMPILE_WARNING:
                $type = 'warning';
                break;
            default:
                $type = 'info';
                break;
        }

        $this->handleParse($errstr, $errfile, $errline, $type);
        return false; // Let PHP handle the error normally
    }

    /**
     * Handle PHP parse analysis.
     * @param string $message The parse message.
     * @param string $file The file where the parse occurred.
     * @param int $line The line number where the parse occurred.
     * @param string $type The parse type (info, warning, success, etc.).
     */
    public function handleParse($message, $file = null, $line = null, $type = 'info')
    {
        if ($this->saveLog) {
            $this->logParse($message, $file, $line, $type);
        }
        self::render($message, $file, $line, $type);
    }

    /**
     * Log the parse information to a file.
     * @param string $message The parse message.
     * @param string $file The file where the parse occurred.
     * @param int $line The line number where the parse occurred.
     * @param string $type The parse type.
     */
    private function logParse($message, $file, $line, $type)
    {
        $logMessage = date('Y-m-d H:i:s') . " | Parse [$type]: $message";
        if ($file) {
            $logMessage .= " | File: $file";
        }
        if ($line) {
            $logMessage .= " | Line: $line";
        }
        $logMessage .= "\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Render the parse report message.
     * @param string $message The parse message.
     * @param string $file The file where the parse occurred.
     * @param int $line The line number where the parse occurred.
     * @param string $type The parse type (info, warning, success, error).
     * @return never
     */
    public static function render($message, $file = null, $line = null, $type = 'info')
    {
        http_response_code(500);
        // Xóa toàn bộ output buffer để chỉ hiển thị trang lỗi
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if ($file === null || $line === null) {
            $backtrace = debug_backtrace();
            $caller = $backtrace[1] ?? $backtrace[0];
            $file = $caller['file'] ?? 'Unknown file';
            $line = $caller['line'] ?? 'Unknown line';
        }

        // Determine colors and icons based on type
        $typeConfig = self::getTypeConfig($type);

        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Parse Report: $message</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background-color: #f8f9fa;
                    color: #333;
                    line-height: 1.5;
                }
                
                .parse-container {
                    margin: 20px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    overflow: hidden;
                }
                @media (max-width: 600px) {
                    .parse-container {
                        margin: 0;
                        border-radius: 0;
                        width: 100vw;
                        min-width: 320px;
                        max-width: 100vw;
                        box-shadow: none;
                    }
                    .parse-title, .code-header, .code-viewer, .parse-meta, .stats-section {
                        padding-left: 8px !important;
                        padding-right: 8px !important;
                    }
                    .parse-title h2 {
                        font-size: 16px !important;
                    }
                    .code-viewer {
                        font-size: 12px !important;
                    }
                }
                
                .parse-header {
                    background: " . $typeConfig['gradient'] . ";
                    color: white;
                    padding: 15px 20px;
                    font-weight: 500;
                    font-size: 14px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .parse-tabs {
                    display: flex;
                    gap: 10px;
                }
                
                .parse-tab {
                    background: rgba(255,255,255,0.2);
                    padding: 4px 12px;
                    border-radius: 4px;
                    font-size: 12px;
                    cursor: pointer;
                    transition: background 0.2s;
                }
                
                .parse-tab:hover {
                    background: rgba(255,255,255,0.3);
                }
                
                .parse-tab.active {
                    background: rgba(255,255,255,0.3);
                }
                
                .parse-content {
                    padding: 0;
                }
                
                .parse-title {
                    padding: 20px;
                    border-bottom: 1px solid #e5e7eb;
                }
                
                .parse-title h2 {
                    color: " . $typeConfig['color'] . ";
                    font-size: 18px;
                    font-weight: 600;
                    margin-bottom: 8px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .parse-icon {
                    font-size: 20px;
                }
                
                .parse-message {
                    color: #6b7280;
                    font-size: 14px;
                }
                
                .parse-meta {
                    background: #f8f9fa;
                    padding: 15px 20px;
                    border-bottom: 1px solid #e5e7eb;
                    font-size: 13px;
                    color: #6b7280;
                }
                
                .meta-item {
                    display: inline-block;
                    margin-right: 20px;
                }
                
                .meta-label {
                    font-weight: 500;
                    color: #374151;
                }
                
                .code-container {
                    background: #fafafa;
                    border-top: 1px solid #e5e7eb;
                }
                
                .code-header {
                    background: #f3f4f6;
                    padding: 12px 20px;
                    border-bottom: 1px solid #e5e7eb;
                    font-size: 12px;
                    color: #6b7280;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .file-path {
                    font-family: 'Monaco', 'Consolas', monospace;
                }
                
                .line-info {
                    color: #9ca3af;
                }
                
                .code-viewer {
                    font-family: 'Monaco', 'Consolas', 'Courier New', monospace;
                    font-size: 13px;
                    line-height: 1.6;
                    background: white;
                    overflow-x: auto;
                }
                
                .code-line {
                    display: flex;
                    min-height: 24px;
                    align-items: center;
                }
                
                .line-number {
                    background: #f8f9fa;
                    color: #9ca3af;
                    padding: 0 12px;
                    text-align: right;
                    min-width: 60px;
                    user-select: none;
                    border-right: 1px solid #e5e7eb;
                    font-size: 12px;
                }
                
                .line-content {
                    padding: 0 16px;
                    flex: 1;
                    white-space: pre;
                }
                
                .highlight-line {
                    background: " . $typeConfig['highlightBg'] . " !important;
                    color: " . $typeConfig['highlightText'] . " !important;
                    font-weight: bold;
                    border-left: 6px solid " . $typeConfig['borderColor'] . ";
                    box-shadow: 0 0 8px " . $typeConfig['shadowColor'] . ";
                }
                
                .highlight-line .line-number {
                    background: " . $typeConfig['highlightBg'] . " !important;
                    color: " . $typeConfig['highlightText'] . " !important;
                    border-right: 1px solid " . $typeConfig['highlightText'] . ";
                }
                
                .highlight-line .line-content {
                    background: " . $typeConfig['contentBg'] . " !important;
                    color: " . $typeConfig['highlightText'] . " !important;
                }
                
                .php-keyword {
                    color: #0ea5e9;
                    font-weight: 500;
                }
                
                .php-variable {
                    color: #d97706;
                }
                
                .php-string {
                    color: #059669;
                }
                
                .php-comment {
                    color: #6b7280;
                    font-style: italic;
                }
                
                .php-function {
                    color: #7c3aed;
                }
                
                .php-constant {
                    color: #dc2626;
                }
                
                .php-operator {
                    color: #374151;
                }
                
                .php-number {
                    color: #ea580c;
                }
                
                .expand-btn {
                    background: none;
                    border: none;
                    color: #6b7280;
                    cursor: pointer;
                    font-size: 12px;
                    padding: 4px;
                    transition: color 0.2s;
                }
                
                .expand-btn:hover {
                    color: #374151;
                }
                
                .stats-section {
                    background: #f8f9fa;
                    padding: 20px;
                    border-top: 1px solid #e5e7eb;
                }
                
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                    gap: 15px;
                }
                
                .stat-item {
                    text-align: center;
                    padding: 10px;
                    background: white;
                    border-radius: 6px;
                    border: 1px solid #e5e7eb;
                }
                
                .stat-value {
                    font-size: 20px;
                    font-weight: 600;
                    color: " . $typeConfig['color'] . ";
                }
                
                .stat-label {
                    font-size: 12px;
                    color: #6b7280;
                    margin-top: 4px;
                }
            </style>
        </head>
        <body>";

        echo "<div class='parse-container'>";
        echo "<div class='parse-header'>";
        echo "<span>" . $typeConfig['headerText'] . "</span>";
        echo "<div class='parse-tabs'>";
        echo "<span class='parse-tab active' onclick='showTab(\"full\")'>Full</span>";
        echo "<span class='parse-tab' onclick='showTab(\"raw\")'>Raw</span>";
        echo "<span class='parse-tab' onclick='showTab(\"stats\")'>Stats</span>";
        echo "</div>";
        echo "</div>";

        echo "<div class='parse-content' id='full-tab'>";
        echo "<div class='parse-title'>";
        echo "<h2><span class='parse-icon'>" . $typeConfig['icon'] . "</span>Parse " . ucfirst($type) . "</h2>";
        echo "<div class='parse-message'>" . htmlspecialchars($message, ENT_NOQUOTES) . "</div>";
        echo "</div>";

        // Meta information
        echo "<div class='parse-meta'>";
        echo "<span class='meta-item'><span class='meta-label'>Timestamp:</span> " . date('Y-m-d H:i:s') . "</span>";
        echo "<span class='meta-item'><span class='meta-label'>Type:</span> " . strtoupper($type) . "</span>";
        if ($file) {
            echo "<span class='meta-item'><span class='meta-label'>Memory:</span> " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</span>";
        }
        echo "</div>";

        if ($file && $line && file_exists($file)) {
            $filename = basename($file);
            echo "<div class='code-container'>";
            echo "<div class='code-header'>";
            echo "<span class='file-path'>/" . $filename . " in " . htmlspecialchars($file, ENT_NOQUOTES) . "</span>";
            echo "<span class='line-info'>at line " . $line . " <button class='expand-btn' onclick='toggleExpand()'>⌄</button></span>";
            echo "</div>";

            echo "<div class='code-viewer' id='code-viewer'>";

            $lines = file($file);
            $start = max($line - 5, 0);
            $end = min($line + 5, count($lines));

            for ($i = $start; $i < $end; $i++) {
                $lineNum = $i + 1;
                $lineContent = rtrim($lines[$i]);
                $isHighlightLine = $lineNum === $line;

                // Simple PHP syntax highlighting
                $highlightedContent = self::highlightPhpSyntax($lineContent);

                $lineClass = $isHighlightLine ? 'code-line highlight-line' : 'code-line';

                echo "<div class='$lineClass'>";
                echo "<div class='line-number'>" . $lineNum . "</div>";
                echo "<div class='line-content'>" . $highlightedContent . "</div>";
                echo "</div>";
            }

            echo "</div>";
            echo "</div>";
        }

        echo "</div>";

        // Raw tab content
        echo "<div class='parse-content' id='raw-tab' style='display: none;'>";
        echo "<div style='padding: 20px; font-family: monospace; background: #f8f9fa; white-space: pre-wrap;'>";
        echo "Message: " . htmlspecialchars($message, ENT_NOQUOTES) . "\n";
        echo "File: " . htmlspecialchars($file ?? 'N/A', ENT_NOQUOTES) . "\n";
        echo "Line: " . ($line ?? 'N/A') . "\n";
        echo "Type: " . htmlspecialchars($type, ENT_NOQUOTES) . "\n";
        echo "Timestamp: " . date('c') . "\n";
        echo "Memory Usage: " . number_format(memory_get_usage(true)) . " bytes\n";
        echo "Peak Memory: " . number_format(memory_get_peak_usage(true)) . " bytes\n";
        echo "</div>";
        echo "</div>";

        // Stats tab content
        echo "<div class='parse-content' id='stats-tab' style='display: none;'>";
        echo "<div class='stats-section'>";
        echo "<div class='stats-grid'>";
        echo "<div class='stat-item'>";
        echo "<div class='stat-value'>" . number_format(memory_get_usage(true) / 1024 / 1024, 1) . "</div>";
        echo "<div class='stat-label'>Memory (MB)</div>";
        echo "</div>";
        echo "<div class='stat-item'>";
        echo "<div class='stat-value'>" . ($line ?? 0) . "</div>";
        echo "<div class='stat-label'>Line Number</div>";
        echo "</div>";
        echo "<div class='stat-item'>";
        echo "<div class='stat-value'>" . number_format(microtime(true) * 1000, 0) . "</div>";
        echo "<div class='stat-label'>Timestamp (ms)</div>";
        echo "</div>";
        echo "<div class='stat-item'>";
        echo "<div class='stat-value'>" . strlen($message) . "</div>";
        echo "<div class='stat-label'>Message Length</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "</div>";

        // Hiển thị các tệp liên quan (included files)
        $includedFiles = get_included_files();
        echo "<div class='related-files-container' style='padding: 0 20px 20px 20px;'>";
        echo "<button class='expand-btn' onclick='toggleRelatedFiles()' id='related-files-btn'>Show Related Files (" . count($includedFiles) . ") ⌄</button>";
        echo "<div id='related-files-list' style='display:none; margin-top:10px;'>";
        echo "<ul style='font-family:monospace; font-size:13px; background:#f8f9fa; border-radius:6px; padding:10px;'>";
        foreach ($includedFiles as $f) {
            $isCurrent = ($f === $file);
            echo "<li" . ($isCurrent ? " style='color:" . $typeConfig['color'] . ";font-weight:bold;'" : "") . ">" . htmlspecialchars($f, ENT_NOQUOTES) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
        echo "</div>";

        // JavaScript for tab switching và toggle file
        echo "<script>
        function showTab(tabName) {
            document.querySelectorAll('[id$=\"-tab\"]').forEach(tab => {
                tab.style.display = 'none';
            });
            document.getElementById(tabName + '-tab').style.display = 'block';
            document.querySelectorAll('.parse-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        function toggleExpand() {
            const codeViewer = document.getElementById('code-viewer');
            const btn = event.target;
            if (codeViewer.style.maxHeight === 'none' || !codeViewer.style.maxHeight) {
                codeViewer.style.maxHeight = '200px';
                codeViewer.style.overflow = 'hidden';
                btn.innerHTML = '⌄';
            } else {
                codeViewer.style.maxHeight = 'none';
                codeViewer.style.overflow = 'visible';
                btn.innerHTML = '⌃';
            }
        }
        function toggleRelatedFiles() {
            var list = document.getElementById('related-files-list');
            var btn = document.getElementById('related-files-btn');
            if (list.style.display === 'none') {
                list.style.display = 'block';
                btn.innerHTML = btn.innerHTML.replace('Show', 'Hide').replace('⌄', '⌃');
            } else {
                list.style.display = 'none';
                btn.innerHTML = btn.innerHTML.replace('Hide', 'Show').replace('⌃', '⌄');
            }
        }
        </script>";

        echo "</body></html>";
        die();
    }

    /**
     * Get configuration for different parse types
     */
    private static function getTypeConfig($type)
    {
        $configs = [
            'success' => [
                'gradient' => 'linear-gradient(135deg, #10b981, #059669)',
                'color' => '#059669',
                'icon' => '✓',
                'headerText' => 'Parse completed successfully!',
                'highlightBg' => '#10b981',
                'highlightText' => '#fff',
                'contentBg' => '#86efac',
                'borderColor' => '#059669',
                'shadowColor' => '#10b98133'
            ],
            'warning' => [
                'gradient' => 'linear-gradient(135deg, #f59e0b, #d97706)',
                'color' => '#d97706',
                'icon' => '⚠',
                'headerText' => 'Parse completed with warnings',
                'highlightBg' => '#f59e0b',
                'highlightText' => '#fff',
                'contentBg' => '#fde68a',
                'borderColor' => '#d97706',
                'shadowColor' => '#f59e0b33'
            ],
            'error' => [
                'gradient' => 'linear-gradient(135deg, #ef4444, #dc2626)',
                'color' => '#dc2626',
                'icon' => '✗',
                'headerText' => 'Parse failed with errors',
                'highlightBg' => '#ef4444',
                'highlightText' => '#fff',
                'contentBg' => '#fca5a5',
                'borderColor' => '#dc2626',
                'shadowColor' => '#ef444433'
            ],
            'info' => [
                'gradient' => 'linear-gradient(135deg, #3b82f6, #2563eb)',
                'color' => '#2563eb',
                'icon' => 'ℹ',
                'headerText' => 'Parse information',
                'highlightBg' => '#3b82f6',
                'highlightText' => '#fff',
                'contentBg' => '#93c5fd',
                'borderColor' => '#2563eb',
                'shadowColor' => '#3b82f633'
            ]
        ];

        return $configs[$type] ?? $configs['info'];
    }

    /**
     * Advanced PHP syntax highlighting (same as ErrorReporting)
     */
    private static function highlightPhpSyntax($code)
    {
        // Remove trailing whitespace but preserve leading whitespace
        $leadingSpace = '';
        if (preg_match('/^(\s+)/', $code, $matches)) {
            $leadingSpace = $matches[1];
        }
        $code = trim($code);

        if (empty($code)) {
            return $leadingSpace;
        }

        $code = htmlspecialchars($code, ENT_NOQUOTES);

        // Handle comments first (to avoid highlighting inside comments)
        $code = preg_replace('/(\/\/.*$)/', '<span class="php-comment">$1</span>', $code);
        $code = preg_replace('/(\/\*.*?\*\/)/s', '<span class="php-comment">$1</span>', $code);

        // Handle strings (single and double quotes)
        $code = preg_replace('/"([^"\\\\]*(\\\\.[^"\\\\]*)*)"/', '<span class="php-string">"$1"</span>', $code);
        $code = preg_replace("/'([^'\\\\]*(\\\\.[^'\\\\]*)*)'/", '<span class="php-string">\'$1\'</span>', $code);

        // Keywords
        $keywords = [
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'callable',
            'case',
            'catch',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'die',
            'do',
            'echo',
            'else',
            'elseif',
            'empty',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'eval',
            'exit',
            'extends',
            'final',
            'finally',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'include',
            'include_once',
            'instanceof',
            'insteadof',
            'interface',
            'isset',
            'list',
            'namespace',
            'new',
            'or',
            'print',
            'private',
            'protected',
            'public',
            'require',
            'require_once',
            'return',
            'static',
            'switch',
            'throw',
            'trait',
            'try',
            'unset',
            'use',
            'var',
            'while',
            'xor',
            'yield',
            'true',
            'false',
            'null'
        ];

        foreach ($keywords as $keyword) {
            $code = preg_replace('/\b(' . preg_quote($keyword) . ')\b(?![^<]*>)/', '<span class="php-keyword">$1</span>', $code);
        }

        // Variables (but not inside already highlighted content)
        $code = preg_replace('/(\$[a-zA-Z_][a-zA-Z0-9_]*)(?![^<]*>)/', '<span class="php-variable">$1</span>', $code);

        // Object operators and array access
        $code = preg_replace('/(-&gt;)/', '<span class="php-operator">-></span>', $code);
        $code = preg_replace('/(::)/', '<span class="php-operator">::</span>', $code);

        // Numbers
        $code = preg_replace('/\b(\d+\.?\d*)\b(?![^<]*>)/', '<span class="php-number">$1</span>', $code);

        // Function calls (word followed by opening parenthesis, but not keywords)
        $code = preg_replace('/\b([a-zA-Z_][a-zA-Z0-9_]*)\s*(?=\()(?![^<]*>)(?!.*<span class="php-keyword">)/', '<span class="php-function">$1</span>', $code);

        // Constants (all caps words)
        $code = preg_replace('/\b([A-Z_][A-Z0-9_]{2,})\b(?![^<]*>)/', '<span class="php-constant">$1</span>', $code);

        // Operators
        $operators = [
            '=',
            '+',
            '-',
            '*',
            '/',
            '%',
            '==',
            '!=',
            '&lt;',
            '&gt;',
            '&lt;=',
            '&gt;=',
            '&amp;&amp;',
            '||',
            '!',
            '&amp;',
            '|',
            '^',
            '&lt;&lt;',
            '&gt;&gt;'
        ];
        foreach ($operators as $op) {
            $pattern = '/(' . preg_quote($op, '/') . ')(?![^<]*>)/';
            $code = preg_replace($pattern, '<span class="php-operator">$1</span>', $code);
        }

        return $leadingSpace . $code;
    }

    /**
     * Quick method to display success message
     */
    public static function success($message, $file = null, $line = null)
    {
        self::render($message, $file, $line, 'success');
    }

    /**
     * Quick method to display warning message
     */
    public static function warning($message, $file = null, $line = null)
    {
        self::render($message, $file, $line, 'warning');
    }

    /**
     * Quick method to display error message
     */
    public static function error($message, $file = null, $line = null)
    {
        self::render($message, $file, $line, 'error');
    }

    /**
     * Quick method to display info message
     */
    public static function info($message, $file = null, $line = null)
    {
        self::render($message, $file, $line, 'info');
    }
}
?>