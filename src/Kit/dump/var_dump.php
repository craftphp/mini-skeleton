<?php
if (!function_exists('dd')) {
    /**
     * Dump and die. A faster coding alternative to var_dump().
     * 
     * Supports multiple variables.
     * @param mixed ...$vars
     * @return never
     */
    function dd(...$vars)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? '';
        $line = $backtrace[0]['line'] ?? 0;
        $src = '';
        if ($file && $line) {
            $lines = @file($file);
            if ($lines && isset($lines[$line-1])) {
                $src = $lines[$line-1];
            }
        }
        $varNames = [];
        if (preg_match('/dd\((.*)\)/', $src, $m)) {
            $varNames = array_map('trim', explode(',', $m[1]));
        }
        $output = '';
        if (empty($vars) && empty($varNames)) {
            $output = "Variable does not exist!\n";
        }
        foreach ($varNames as $i => $raw) {
            $raw = trim($raw);
            if (preg_match('/^[$][a-zA-Z_][a-zA-Z0-9_]*$/', $raw)) {
                if (array_key_exists($i, $vars)) {
                    $output .= $raw . ' = ' . craft_custom_var_dump($vars[$i]) . "\n";
                } else {
                    $output .= $raw . ' = *UNDEFINED*' . "\n";
                }
            } else {
                if (array_key_exists($i, $vars)) {
                    $output .= craft_custom_var_dump($vars[$i]) . "\n";
                } else {
                    $output .= '*UNDEFINED*' . "\n";
                }
            }
        }

        if (count($vars) > count($varNames)) {
            for ($j = count($varNames); $j < count($vars); $j++) {
                $output .= craft_custom_var_dump($vars[$j]) . "\n";
            }
        }
        $isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');
        if ($isCli) {
            echo $output . PHP_EOL;
        } else {
            echo '<pre style="background:#222;color:#eee;padding:5px 10px;border-radius:8px;overflow:auto;font-size:13px;line-height:1.2;box-shadow:0 2px 8px #0002;">' . htmlspecialchars($output) . '</pre>';
        }
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump only. A faster coding alternative to var_dump().
     * 
     * Supports multiple variables.
     * @param mixed ...$vars
     * @return void
     */
    function dump(...$vars)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? '';
        $line = $backtrace[0]['line'] ?? 0;
        $src = '';
        if ($file && $line) {
            $lines = @file($file);
            if ($lines && isset($lines[$line-1])) {
                $src = $lines[$line-1];
            }
        }
        $varNames = [];
        if (preg_match('/dump\((.*)\)/', $src, $m)) {
            $varNames = array_map('trim', explode(',', $m[1]));
        }
        $output = '';
        if (empty($vars) && empty($varNames)) {
            $output = "Variable does not exist!\n";
        }
        foreach ($varNames as $i => $raw) {
            $raw = trim($raw);
            if (preg_match('/^[$][a-zA-Z_][a-zA-Z0-9_]*$/', $raw)) {
                if (array_key_exists($i, $vars)) {
                    $output .= $raw . ' = ' . craft_custom_var_dump($vars[$i]) . "\n";
                } else {
                    $output .= $raw . ' = *UNDEFINED*' . "\n";
                }
            } else {
                if (array_key_exists($i, $vars)) {
                    $output .= craft_custom_var_dump($vars[$i]) . "\n";
                } else {
                    $output .= '*UNDEFINED*' . "\n";
                }
            }
        }

        if (count($vars) > count($varNames)) {
            for ($j = count($varNames); $j < count($vars); $j++) {
                $output .= craft_custom_var_dump($vars[$j]) . "\n";
            }
        }
        $isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');
        if ($isCli) {
            echo $output . PHP_EOL;
        } else {
            echo '<pre style="background:#222;color:#eee;padding:5px 10px;border-radius:8px;overflow:auto;font-size:13px;line-height:1.2;box-shadow:0 2px 8px #0002;">' . htmlspecialchars($output) . '</pre>';
        }
    }
}

/**
 * Custom var_dump function that supports HTML output and recursion detection.
 * 
 * @param mixed $var The variable to dump.
 * @param int $indent The current indentation level.
 * @param array $references Array to track references for recursion detection.
 * @return string
 */
function craft_custom_var_dump($var, $indent = 0, &$references = []): string
{
    $indentation = str_repeat("  ", $indent);
    $varKey = null;
    if (is_object($var)) {
        $varKey = spl_object_hash($var);
    } elseif (is_array($var)) {
        $varKey = md5(json_encode($var, JSON_PARTIAL_OUTPUT_ON_ERROR));
    }
    if ($varKey && in_array($varKey, $references)) {
        return "{$indentation}*RECURSION*\n";
    }
    if ($varKey) {
        $references[] = $varKey;
    }
    $out = '';
    if (is_null($var)) {
        $out = "{$indentation}NULL\n";
    } elseif (is_bool($var)) {
        $out = "{$indentation}bool(" . ($var ? 'true' : 'false') . ")\n";
    } elseif (is_int($var)) {
        $out = "{$indentation}int($var)\n";
    } elseif (is_float($var)) {
        $out = "{$indentation}float($var)\n";
    } elseif (is_string($var)) {
        $out = "{$indentation}string(" . strlen($var) . ") \"$var\"\n";
    } elseif (is_array($var)) {
        $out = "{$indentation}array(" . count($var) . ") {\n";
        foreach ($var as $key => $value) {
            $line = "{$indentation}  [" . (is_string($key) ? "\"$key\"" : $key) . "]=>\n";
            $out .= $line . craft_custom_var_dump($value, $indent + 1, $references);
        }
        $out .= "{$indentation}}}\n";
    } elseif (is_object($var)) {
        $className = get_class($var);
        $out = "{$indentation}object($className) {\n";
        $properties = (array) $var;
        foreach ($properties as $key => $value) {
            $line = "{$indentation}  [$key]=>\n";
            $out .= $line . craft_custom_var_dump($value, $indent + 1, $references);
        }
        $out .= "{$indentation}}}\n";
    } elseif (is_resource($var)) {
        $out = "{$indentation}resource(" . get_resource_type($var) . ")\n";
    } else {
        $out = "{$indentation}unknown type\n";
    }
    return $out;
}