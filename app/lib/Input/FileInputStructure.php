<?php

namespace App\Lib\Input;

use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileInputStructure
{

    /**
     * @param string[] $allowed_mime_types
     */
    function __construct(

        public array $allowed_mime_types = [],
        public ?int $byte_min_size = null,
        public ?int $byte_max_size = 8 * 1024 * 1024,

    ) {}

    function isValid(UploadedFile $file): bool
    {
        if (!$file->isValid()) {
            return false;
        }

        $size = $file->getSize();

        if ($size === false) {
            return false;
        }

        if ($this->byte_min_size !== null && $size < $this->byte_min_size) {
            return false;
        }

        if ($this->byte_max_size !== null && $size > $this->byte_max_size) {
            return false;
        }

        if ($this->allowed_mime_types !== []) {
            $mime = $file->getMimeType();

            if ($mime === null || !in_array($mime, $this->allowed_mime_types, true)) {
                return false;
            }
        }

        return true;
    }

}
