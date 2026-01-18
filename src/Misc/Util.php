<?php

namespace Hizech\Bliss\Misc;

use Hizech\Bliss\Html\HtmlType;
use DateTimeZone;
use ErrorException;

class Util
{

    /** @var array<int, string>|null */
    private static ?array $cached_php_timezones = null;

    /**
     * @return array<int, class-string>
     */
    public static function findAndLoadIfSubclass(string $dir, string $parentClass): array
    {
        $subclasses = [];

        if (!is_dir($dir)) {
            return $subclasses;
        }

        // Normalize directory path
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        // Find all PHP files recursively
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Try to load the file
                require_once $file->getPathname();
            }
        }

        // Now that all files are loaded, find subclasses of the parent class
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, $parentClass)) {
                $reflection = new \ReflectionClass($class);
                if ($reflection->isInstantiable()) {
                    $subclasses[] = $class;
                }
            }
        }

        return $subclasses;
    }

    /**
     * @return array<int, string>
     */
    public static function cachedPhpTimezones(): array
    {
        if (self::$cached_php_timezones === null) {
            $timezones = DateTimeZone::listIdentifiers();
            self::$cached_php_timezones = $timezones;
        }

        return self::$cached_php_timezones;
    }

    // Returns full absolute path, given path relative to root
    // Will look like '/home/root', slash at the start, no slash at the end

    /**
     * Normalizes a filesystem path for cross-platform consistency.
     *
     * - Converts backslashes to forward slashes.
     * - Collapses duplicate slashes.
     * - Preserves Windows drive letters.
     * - Ensures consistent leading/trailing slash rules.
     *
     * Examples:
     *   "C:\\Users\\Admin\\Documents\\foo\\" → "C:/Users/Admin/Documents/foo"
     *   "\\app\\twig\\auto-templates\\"      → "/app/twig/auto-templates"
     *   "//var//www/html"                   → "/var/www/html"
     *
     * @param string $path The input filesystem path.
     * @return string The normalized, OS-independent path.
     */
    public static function normalizePath(string $path): string
    {
        // Replace backslashes with forward slashes (Windows → Unix)
        $normalized = str_replace('\\', '/', $path);

        // Collapse duplicate slashes (e.g., // → /)
        $normalized = preg_replace('#/+#', '/', $normalized);

        // Trim spaces around
        $normalized = trim($normalized);

        // If Windows drive letter present (e.g., C:/), keep it intact
        if (preg_match('#^[A-Za-z]:/#', $normalized)) {
            // Normalize everything *after* the drive colon
            [$drive, $rest] = explode(':', $normalized, 2);
            $normalized = strtoupper($drive) . ':' . preg_replace('#/+#', '/', $rest);
        } else {
            // Ensure single leading slash for absolute paths
            if (!str_starts_with($normalized, '/')) {
                $normalized = '/' . ltrim($normalized, '/');
            }
        }

        // Remove trailing slash (except for root or drive root)
        if (strlen($normalized) > 1 && str_ends_with($normalized, '/')) {
            $normalized = rtrim($normalized, '/');
        }

        return $normalized;
    }

    /**
     * @param string ...$parts
     */
    static public function pathByParts(string ...$parts): string
    {
        $str = '';
        $regex = '/^(?:\/)+[a-zA-Z0-9_.-]+$/';

        foreach ($parts as $part) {
            if (preg_match($regex, $part) !== 1) throw new ErrorException('Invalid part, must start with a slash, never end with one, not have them in the middle, and directory and file names are character strict.');
            $str .= '/' . trim($part, '/');
            $str = str_replace('/', DIRECTORY_SEPARATOR, $str);
        }

        return $str;
    }

    /**
     * Join path parts with OS-appropriate directory separator.
     *
     * Unlike pathByParts(), this accepts plain strings without leading slashes.
     * Empty parts are filtered out. Existing separators are normalized.
     *
     * Examples:
     *   joinPath('/home/user', 'app', 'config') → '/home/user/app/config' (Linux)
     *   joinPath('C:\Users', 'app', 'config')   → 'C:\Users\app\config' (Windows)
     *   joinPath($root, 'plugins', $vendor, $name) → proper OS path
     *
     * @param string ...$parts Path parts to join
     * @return string Joined path with OS-appropriate separators
     */
    public static function joinPath(string ...$parts): string
    {
        // Filter out empty parts
        $parts = array_filter($parts, fn($p) => $p !== '');

        if (empty($parts)) {
            return '';
        }

        // Normalize each part: convert all separators to OS separator, trim trailing separators
        $normalized = [];
        foreach ($parts as $i => $part) {
            // Convert all slashes to OS separator
            $part = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $part);

            // Trim trailing separators (but preserve leading for first part if absolute path)
            if ($i === 0) {
                $part = rtrim($part, DIRECTORY_SEPARATOR);
            } else {
                $part = trim($part, DIRECTORY_SEPARATOR);
            }

            if ($part !== '') {
                $normalized[] = $part;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $normalized);
    }

    static public function inputTemplate(
        HtmlType $type,
        string $attrs_placeholder,
        string $value_placeholder
    ): string {

        $template = match ($type) {
            HtmlType::Text     => '<input type="text" value="' . $value_placeholder . '" ' . $attrs_placeholder . '>',
            HtmlType::Email    => '<input type="email" value="' . $value_placeholder . '" ' . $attrs_placeholder . '>',
            HtmlType::Password => '<input type="password" value="' . $value_placeholder . '" ' . $attrs_placeholder . '>',
            HtmlType::Number   => '<input type="number" value="' . $value_placeholder . '" ' . $attrs_placeholder . '>',
            HtmlType::Checkbox => '<input type="checkbox" value="' . $value_placeholder . '" ' . $attrs_placeholder . '>',
            HtmlType::Radio    => '<input type="radio" value="' . $value_placeholder . '" ' . $attrs_placeholder . '>',
            HtmlType::Select   => '<select ' . $attrs_placeholder . '>' . $value_placeholder . '</select>',
            HtmlType::Textarea => '<textarea ' . $attrs_placeholder . '>' . $value_placeholder . '</textarea>',
           HtmlType::File     => '<input type="file" ' . $attrs_placeholder . '>',
            HtmlType::Hidden   => '<input type="hidden" value="' . $value_placeholder . '" ' . $attrs_placeholder . '>',
            HtmlType::Submit   => '<input type="submit" value="' . $value_placeholder . '" ' . $attrs_placeholder . '>',
        };

        return $template;

    }

    public static function emptyDirectory(string $dir) : void {
        if (!is_dir($dir)) {
            return; // Directory doesn't exist, nothing to empty
        }

        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                self::deleteDirectory($path); // Recursively delete subdirectories
            } else {
                unlink($path); // Delete files
            }
        }
    }

    public static function deleteDirectory(string $dir) : void {
        if (!is_dir($dir)) {
            return; // Directory doesn't exist, nothing to delete
        }

        self::emptyDirectory($dir); // First empty the directory
        rmdir($dir); // Then remove the directory itself
    }

    public static function echoCliLine(string $str) : void
    {
        echo PHP_EOL . $str . PHP_EOL;
    }

}