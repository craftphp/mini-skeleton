<?php
namespace Craft\Reports;

/**
 * #### RuntimeReporting class for handling fatal errors in web applications.
 *
 * This class captures fatal errors, logs them if configured, and renders a user-friendly error page.
 *
 */
class CraftRuntime
{
    private $saveLog;
    private $logFile;

    /**
     * RuntimeReporting is a class for handling fatal errors in web applications.
     *
     * @param bool $saveLog Whether to save error logs to a file
     * @param string $logFile The file path to save logs
     */
    public function __construct($saveLog = false, $logFile = 'runtime.log')
    {
        $this->saveLog = $saveLog;
        $this->logFile = $logFile;
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null) {
            $errno = $error['type'];
            $errfile = $error['file'];
            $errline = $error['line'];
            $errstr = $error['message'];

            if ($this->saveLog) {
                $this->logError($errno, $errstr, $errfile, $errline);
            }

            self::render($errstr, $errfile, $errline, $errno);
            exit(1);
        }
    }

    private function logError($errno, $errstr, $errfile, $errline)
    {
        $directory = dirname($this->logFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $logMessage = date('Y-m-d H:i:s') . " | Runtime Error [$errno]: $errstr | File: $errfile | Line: $errline\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Signature method to create a new instance of RuntimeReporting.
     * @param bool $saveLog
     * @param string $logFile
     * @return self
     */
    public static function sign($saveLog = false, $logFile = 'runtime.log')
    {
        return new self($saveLog, $logFile);
    }

    public static function render($message, $file = null, $line = null, $errno = null)
    {
        http_response_code(500);
        // Xóa toàn bộ output buffer để chỉ hiển thị trang lỗi
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if ($file === null || $line === null) {
            $backtrace = debug_backtrace();
            $caller = $backtrace[0];
            $file = $caller['file'] ?? 'Unknown file';
            $line = $caller['line'] ?? 'Unknown line';
        }

        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Fatal Error: $message </title>
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
                
                .error-container {
                    margin: 20px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    overflow: hidden;
                }
                @media (max-width: 600px) {
                    .error-container {
                        margin: 0;
                        border-radius: 0;
                        width: 100vw;
                        min-width: 320px;
                        max-width: 100vw;
                        box-shadow: none;
                    }
                    .error-title, .code-header, .code-viewer {
                        padding-left: 8px !important;
                        padding-right: 8px !important;
                    }
                    .error-title h2 {
                        font-size: 16px !important;
                    }
                    .code-viewer {
                        font-size: 12px !important;
                    }
                }
                
                .error-header {
                    background: linear-gradient(135deg, #f59e0b, #d97706);
                    color: white;
                    padding: 15px 20px;
                    font-weight: 500;
                    font-size: 14px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .error-tabs {
                    display: flex;
                    gap: 10px;
                }
                
                .error-tab {
                    background: rgba(255,255,255,0.2);
                    padding: 4px 12px;
                    border-radius: 4px;
                    font-size: 12px;
                    cursor: pointer;
                }
                
                .error-tab.active {
                    background: rgba(255,255,255,0.3);
                }
                
                .error-content {
                    padding: 0;
                }
                
                .error-title {
                    padding: 20px;
                    border-bottom: 1px solid #e5e7eb;
                }
                
                .error-title h2 {
                    color: #f59e0b;
                    font-size: 18px;
                    font-weight: 600;
                    margin-bottom: 8px;
                }
                
                .error-message {
                    color: #6b7280;
                    font-size: 14px;
                }
                
                .error-type {
                    background: #fef3c7;
                    color: #92400e;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    font-weight: 500;
                    display: inline-block;
                    margin-bottom: 8px;
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
                
                .error-line {
                    background: #fef3c7 !important;
                    color: #92400e !important;
                    font-weight: bold;
                    border-left: 6px solid #f59e0b;
                    box-shadow: 0 0 8px #f59e0b33;
                }
                .error-line .line-number {
                    background: #fef3c7 !important;
                    color: #92400e !important;
                    border-right: 1px solid #f59e0b;
                }
                .error-line .line-content {
                    background: #fef3c7 !important;
                    color: #92400e !important;
                }
                
                .php-keyword {
                    color: #0ea5e9;
                    font-weight: 500;
                }
                
                .php-variable {
                    color: rgb(153, 143, 64);
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
                
                .php-operator {
                    color: #374151;
                }
                
                .php-number {
                    color: #059669;
                }
                
                .php-constant {
                    color: #7c2d12;
                }
                
                .expand-btn {
                    background: none;
                    border: none;
                    color: #6b7280;
                    cursor: pointer;
                    font-size: 12px;
                    padding: 4px;
                }
            </style>
        </head>
        <body>";

        echo "<div class='error-container'>";
        echo "<div class='error-header'>";
        echo "<span>Fatal runtime error detected!</span>";
        echo "<div class='error-tabs'>";
        echo "<span class='error-tab active' onclick='showTab(\"full\")'>Full</span>";
        echo "<span class='error-tab' onclick='showTab(\"raw\")'>Raw</span>";
        echo "</div>";
        echo "</div>";

        // Full tab content
        echo "<div class='error-content' id='full-tab'>";
        echo "<div class='error-title'>";
        echo "<div class='error-type'>FATAL ERROR [" . ($errno ?? 'Unknown') . "]</div>";
        echo "<h2>Runtime Error</h2>";
        echo "<div class='error-message'>" . htmlspecialchars($message, ENT_NOQUOTES) . "</div>";
        echo "</div>";

        if ($file && $line && file_exists($file)) {
            $filename = basename($file);
            echo "<div class='code-container'>";
            echo "<div class='code-header'>";
            echo "<span class='file-path'>/" . $filename . " in " . htmlspecialchars($file, ENT_NOQUOTES) . "</span>";
            echo "<span class='line-info'>at line " . $line . " <button class='expand-btn'>⌄</button></span>";
            echo "</div>";

            echo "<div class='code-viewer'>";

            $lines = file($file);
            $start = max($line - 5, 0);
            $end = min($line + 5, count($lines));

            for ($i = $start; $i < $end; $i++) {
                $lineNum = $i + 1;
                $lineContent = rtrim($lines[$i]);
                $isErrorLine = $lineNum === $line;

                // Simple PHP syntax highlighting
                $highlightedContent = self::highlightPhpSyntax($lineContent);

                $lineClass = $isErrorLine ? 'code-line error-line' : 'code-line';

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
        echo "<div class='error-content' id='raw-tab' style='display: none;'>";
        echo "<div style='padding: 20px; font-family: monospace; background: #f8f9fa; white-space: pre-wrap;'>";
        echo "Message: " . htmlspecialchars($message, ENT_NOQUOTES) . "\n";
        echo "File: " . htmlspecialchars($file ?? 'N/A', ENT_NOQUOTES) . "\n";
        echo "Line: " . ($line ?? 'N/A') . "\n";
        echo "Error Number: " . ($errno ?? 'N/A') . "\n";
        echo "Timestamp: " . date('c') . "\n";
        echo "Memory Usage: " . number_format(memory_get_usage(true)) . " bytes\n";
        echo "Peak Memory: " . number_format(memory_get_peak_usage(true)) . " bytes\n";
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
            echo "<li" . ($isCurrent ? " style='color:#d97706;font-weight:bold;'" : "") . ">" . htmlspecialchars($f, ENT_NOQUOTES) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
        echo "</div>";

        // JavaScript for tab switching
        echo "<script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('[id$=\"-tab\"]').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Update active tab styling
            document.querySelectorAll('.error-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
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

        echo "</div>";
        echo "</div>";
        echo "</body></html>";
        die();
    }

    /**
     * Advanced PHP syntax highlighting
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
}
?>