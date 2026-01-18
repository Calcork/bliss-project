<?php

namespace Hizech\Bliss\Input\Util;

use Hizech\Bliss\Input\BoolInput;
use Hizech\Bliss\Input\FloatInput;
use Hizech\Bliss\Input\IntInput;
use Hizech\Bliss\Input\StringInput;
use Hizech\Bliss\Input\UploadedFileInput;

class Html
{
    /**
     * @return array<string, string>
     */
    public static function getAttrsBySpecificInput(
        UploadedFileInput|BoolInput|StringInput|FloatInput|IntInput $input
    ): array {
        return match (true) {
            $input instanceof UploadedFileInput => self::getUploadedFileAttrs($input),
            $input instanceof BoolInput         => self::getBoolAttrs(),
            $input instanceof IntInput          => self::getIntAttrs(),
            $input instanceof FloatInput        => self::getFloatAttrs(),
            default                             => self::getStringAttrs($input),
        };
    }

    /**
     * @return array<string, string>
     */
    private static function getUploadedFileAttrs(UploadedFileInput $input): array
    {
        $attrs = [
            'type' => 'file',
            'data-max-size' => htmlspecialchars((string) $input->max_size_bytes, ENT_QUOTES),
        ];

        if ($input->allowed_extensions !== null) {
            $accept = implode(',', array_map(fn($ext) => '.' . $ext, $input->allowed_extensions));
            $attrs['accept'] = htmlspecialchars($accept, ENT_QUOTES);
        }

        return $attrs;
    }

    /**
     * @return array<string, string>
     */
    private static function getBoolAttrs(): array
    {
        return [
            'type' => 'checkbox',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getIntAttrs(): array
    {
        return [
            'type' => 'number',
            'step' => '1',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getFloatAttrs(): array
    {
        return [
            'type' => 'number',
            'step' => 'any',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getStringAttrs(StringInput $input): array
    {
        $attrs = [
            'type' => 'text',
        ];

        if ($input->min_length !== null) {
            $attrs['minlength'] = (string) $input->min_length;
        }

        if ($input->max_length !== null) {
            $attrs['maxlength'] = (string) $input->max_length;
        }

        if (is_string($input->regex) && !empty($input->regex)) {
            $regex = $input->regex;

            // Remove delimiters (e.g., /.../u)
            if (preg_match('/^(.)(.*)\1([a-z]*)$/i', $regex, $m)) {
                $regex = $m[2];
            }

            $attrs['pattern'] = htmlspecialchars($regex, ENT_QUOTES, 'UTF-8');
        }

        if ($input->choices !== null) {
            $choices = implode(',', $input->choices);
            $attrs['data-choices'] = htmlspecialchars($choices, ENT_QUOTES);
        }

        return $attrs;
    }
}
