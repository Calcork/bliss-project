<?php

namespace App\Lib\Http;

use App\Lib\Input\BoolInputStructure;
use App\Lib\Input\FileInputStructure;
use App\Lib\Input\FloatInputStructure;
use App\Lib\Input\IntInputStructure;
use App\Lib\Input\StringInputStructure;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class Inputs
{
    /**
     * Parses inputs given expectations and expected structures. Invalid inputs are unset, will be missing from the returned array.
     * @param array<string, BoolInputStructure|StringInputStructure|IntInputStructure|FloatInputStructure|FileInputStructure> $arguments_structure
     * @param array<string, bool> $arguments_is_array
     * @return array<string, int|float|bool|string|UploadedFile|int[]|float[]|string[]|bool[]|UploadedFile[]>
     */
    static function normalizeAndValidateHttpInputs(Request $request, array $arguments_structure, array $arguments_is_array): array
    {
        $result = [];

        foreach ($arguments_structure as $name => $structure) {
            $is_array = $arguments_is_array[$name] ?? false;

            if ($structure instanceof FileInputStructure) {
                $files = $request->files->get($name);

                if ($is_array) {
                    if (!is_array($files)) {
                        continue;
                    }
                    $valid = [];
                    foreach ($files as $file) {
                        if ($file instanceof UploadedFile && $structure->isValid($file)) {
                            $valid[] = $file;
                        }
                    }
                    if ($valid !== []) {
                        $result[$name] = $valid;
                    }
                } else {
                    if ($files instanceof UploadedFile && $structure->isValid($files)) {
                        $result[$name] = $files;
                    }
                }
                continue;
            }

            if ($is_array) {
                $raw_array = $request->request->all($name) ?: $request->query->all($name);
                $valid = [];
                foreach ($raw_array as $item) {
                    $parsed = self::parseAndValidate($structure, (string) $item);
                    if ($parsed !== null) {
                        $valid[] = $parsed;
                    }
                }
                if ($valid !== []) {
                    $result[$name] = $valid;
                }
            } else {
                $raw = $request->request->get($name) ?? $request->query->get($name);
                if ($raw === null) {
                    continue;
                }
                $parsed = self::parseAndValidate($structure, (string) $raw);
                if ($parsed !== null) {
                    $result[$name] = $parsed;
                }
            }
        }

        return $result;
    }

    /**
     * Parses inputs given expectations and expected structures. Invalid inputs are unset, will be missing from the returned array.
     * @param array<string, string> $uri_params
     * @param array<string, BoolInputStructure|StringInputStructure|IntInputStructure|FloatInputStructure> $arguments_structure
     * @return array<string, int|float|bool|string>
     */
    static function normalizeAndValidateUriParamsInputs(array $uri_params, array $arguments_structure): array
    {
        $result = [];

        foreach ($arguments_structure as $name => $structure) {
            $raw = $uri_params[$name] ?? null;

            if ($raw === null) {
                continue;
            }

            $parsed = self::parseAndValidate($structure, $raw);
            if ($parsed !== null) {
                $result[$name] = $parsed;
            }
        }

        return $result;
    }

    static private function parseAndValidate(
        BoolInputStructure|StringInputStructure|IntInputStructure|FloatInputStructure $structure,
        string $raw,
    ): int|float|bool|string|null
    {
        if ($structure instanceof StringInputStructure) {
            return $structure->isValid($raw) ? $raw : null;
        }

        if ($structure instanceof IntInputStructure) {
            $value = filter_var($raw, FILTER_VALIDATE_INT);
            if ($value === false) {
                return null;
            }
            return $structure->isValid($value) ? $value : null;
        }

        if ($structure instanceof FloatInputStructure) {
            $value = filter_var($raw, FILTER_VALIDATE_FLOAT);
            if ($value === false) {
                return null;
            }
            return $structure->isValid($value) ? $value : null;
        }

        // BoolInputStructure
        return match ($raw) {
            '1', 'true' => true,
            '0', 'false' => false,
            default => null,
        };
    }

}
