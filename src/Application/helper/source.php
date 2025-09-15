<?php
/**
 * #### Class Source
 * 
 * A utility class for managing source files located in public/source.
 */
class Source {
    /**
     * Get the URL for a source file (located in public/source).
     * Usage:
     *   Source::url('css/main.css')
     *   Source::url(['file' => 'css.main', 'extension' => 'css'])
     *   Source::url('js.main', 'js')
     *
     * @param string|array $file The source file
     * @param string|null $extension The file extension
     * @return string
     */
    public static function url($file = '', ?string $extension = null): string
    {
        // Support array or named arguments
        if (is_array($file)) {
            $filename = $file['file'] ?? '';
            $ext = $file['extension'] ?? '';
        } else {
            $filename = $file;
            $ext = $extension ?? '';
        }

        // If using dot notation: css.main, js.main
        if ($ext && strpos($filename, '.') !== false && strpos($filename, '/') === false) {
            $parts = explode('.', $filename);
            if (count($parts) === 2) {
                $filename = $parts[0] . '/' . $parts[1] . '.' . $ext;
            }
        }

        // If not dot notation, just append extension if missing
        if ($ext && substr($filename, -strlen($ext) - 1) !== '.' . $ext) {
            $filename .= '.' . $ext;
        }

        $path = ltrim(str_replace(['..', '\\'], '', $filename), '/');

        return '/source/' . $path;
    }

    /**
     * Get the file URL for a source file (located in public/source).
     * @param mixed $file The source file
     * @param mixed $extension The file extension
     * @return string
     */
    public static function file($file = '', $extension = null): string
    {
        return self::url($file, $extension);
    }

    /**
     * Check if a source file exists in public/source.
     * @param string|array $file The source file
     * @param bool $showInfo If true, return file information
     * @return bool|array
     */
    public static function check($file = '', bool $showInfo = false) {
        $baseDir = INDEX_DIR;
        $relativePath = ltrim(self::url($file), '/');
        $filePath = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . $relativePath;
        if (!file_exists($filePath)) {
            return false;
        }
        if ($showInfo) {
            return [
                'path' => $filePath,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
                'is_readable' => is_readable($filePath) ? 'true' : 'false',
                'is_writable' => is_writable($filePath) ? 'true' : 'false',
                'is_executable' => is_executable($filePath) ? 'true' : 'false'
            ];
        }
        return true;
    }

    // public static function upload($file = '', $location) {
        
    // }

    // public static function change($fileChange = '', $fileReplace = '') {

    // }

    // public static function remove($file = '', $location) {

    // }
}