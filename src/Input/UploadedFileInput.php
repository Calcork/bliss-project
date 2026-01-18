<?php

namespace Hizech\Bliss\Input;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileInput
{

    /** @var array<int, string>|null */
    public readonly ?array $allowed_extensions;

    /**
     * @param array<int, string>|null $allowed_extensions list of extensions without dot, or null to not be specific
     */
    function __construct(
        public readonly ?int $max_size_bytes = null,
        ?array $allowed_extensions = null,
    ) {
        if(is_array($allowed_extensions)) {
            $a = [];
            foreach ($allowed_extensions as $allowed_extension) {
                $a[] = self::normalizeExtension($allowed_extension);
            }
            $this->allowed_extensions = $a;
        }
        else $this->allowed_extensions = null;
    }

    function validate(UploadedFile $value) : null|UploadedFile {

        if (!$value->isValid()) {
            return null;
        }

        if ($value->getSize() > $this->max_size_bytes) {
            return null;
        }

        $normalized_extension = self::normalizeExtension($value->getExtension());

        if(is_array($this->allowed_extensions) && !in_array($normalized_extension, $this->allowed_extensions, true)) return null;
        return $value;

    }

    // returns extension normalized ['jpg', 'exe']
    static function normalizeExtension(string $extension) : ?string {

            // Remove leading dots if present
            $ext = ltrim($extension, '.');

            // Trim whitespace
            $ext = trim($ext);

            // Skip empty strings
            if ($ext === '') {
                return null;
            }

            // Convert to lowercase
            $ext = strtolower($ext);

            // Normalize common aliases to canonical form (based on IANA/common practice)
            $ext = match($ext) {
                'jpeg' => 'jpg',
                'tiff' => 'tif',
                'html' => 'htm',
                'mpeg' => 'mpg',
                'javascript' => 'js',
                'typescript' => 'ts',
                default => $ext
            };

            return $ext;

    }

}