<?php
/**
 * #### Class Source
 *
 * Utility class for managing source files located in public/source.
 */
class Source
{
    /** @var string Source folder name */
    private const SOURCE_DIR = 'source';

    /**
     * Build URL for a source file (located in public/source).
     *
     * @param string|array $file File path or config array
     * @param string|null $extension File extension
     * @return string
     */
    public static function url($file = '', ?string $extension = null): string
    {
        if (is_array($file)) {
            $filename = $file['file'] ?? '';
            $ext = $file['extension'] ?? '';
        } else {
            $filename = (string) $file;
            $ext = $extension ?? '';
        }

        // Handle dot notation: css.main → css/main.css
        if ($ext && strpos($filename, '.') !== false && strpos($filename, '/') === false) {
            $parts = explode('.', $filename, 2);
            if (count($parts) === 2) {
                $filename = $parts[0] . '/' . $parts[1];
            }
        }

        // Append extension if missing
        $info = pathinfo($filename);
        if ($ext && (!isset($info['extension']) || $info['extension'] !== $ext)) {
            $filename .= '.' . $ext;
        }

        // Sanitize path
        $path = preg_replace('#[^a-zA-Z0-9/_\.-]#', '', $filename);
        $path = ltrim($path, '/');

        return '/' . self::SOURCE_DIR . '/' . $path;
    }

    /**
     * Shortcut for url()
     */
    public static function file($file = '', $extension = null): string
    {
        return self::url($file, $extension);
    }

    /**
     * Get the full path for a source file.
     *
     * @param string|array $file File name
     * @return string
     */
    public static function path($file = ''): string
    {
        return rtrim(INDEX_DIR, '/\\') . self::url($file);
    }

    /**
     * Check if a source file exists in public/source.
     *
     * @param string|array $file File name
     * @param bool $showInfo Return file info if true
     * @return bool|array
     */
    public static function check($file = '', bool $showInfo = false)
    {
        $baseDir = rtrim(INDEX_DIR, '/\\') . DIRECTORY_SEPARATOR . self::SOURCE_DIR;
        $relativePath = ltrim(self::url($file), '/');
        $filePath = rtrim(INDEX_DIR, '/\\') . DIRECTORY_SEPARATOR . $relativePath;

        $realBase = realpath($baseDir);
        $realPath = realpath($filePath);

        if ($realPath === false || strpos($realPath, $realBase) !== 0) {
            return false;
        }

        if ($showInfo) {
            return [
                'path' => $realPath,
                'size' => filesize($realPath),
                'modified' => filemtime($realPath),
                'is_readable' => is_readable($realPath),
                'is_writable' => is_writable($realPath),
                'is_executable' => is_executable($realPath)
            ];
        }
        return true;
    }

    /**
     * Upload a file to a specified location within public/source.
     *
     * @param array $file File from $_FILES
     * @param string $location Target subdirectory inside /source
     * @return bool|string Path of uploaded file or false
     */
    public static function upload(array $file, string $location = '')
    {
        if (!isset($file['tmp_name'], $file['name'])) {
            return false;
        }

        $targetDir = rtrim(INDEX_DIR, '/\\') . '/' . self::SOURCE_DIR . '/' . trim($location, '/\\');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetFile = $targetDir . '/' . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $targetFile;
        }
        return false;
    }

    /**
     * Rename a file in public/source.
     *
     * @param string $oldFile Old file name
     * @param string $newFile New file name
     * @return bool
     */
    public static function rename(string $oldFile, string $newFile): bool
    {
        $oldPath = rtrim(INDEX_DIR, '/\\') . self::url($oldFile);
        $newPath = rtrim(INDEX_DIR, '/\\') . self::url($newFile);

        if (!file_exists($oldPath)) {
            return false;
        }

        $dir = dirname($newPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return rename($oldPath, $newPath);
    }

    /**
     * Remove a file from public/source.
     *
     * @param string $file File name
     * @return bool
     */
    public static function remove(string $file): bool
    {
        $filePath = rtrim(INDEX_DIR, '/\\') . self::url($file);
        return file_exists($filePath) ? unlink($filePath) : false;
    }

    /**
     * Replace an old file with a new file.
     *
     * @param string $oldFile Old file name
     * @param array $newFile New file (from $_FILES)
     * @return bool|string Path of new file or false
     */
    public static function change(string $oldFile, array $newFile)
    {
        if (!self::remove($oldFile)) {
            return false;
        }

        $location = dirname($oldFile);
        return self::upload($newFile, $location);
    }
}
