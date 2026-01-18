<?php

namespace Hizech\Bliss\Html;

class HtmlInput
{

    static public function renderTextarea(string $attrs, ?string $value): string
    {
        $value_str = (is_string($value)) ? $value : '';
        return "<textarea {$attrs}>{$value_str}</textarea>";
    }


    /**
     * @param array<string, mixed> $attrs
     */
    public static function buildAttrs(array $attrs): string
    {
        $str = '';

        foreach ($attrs as $key => $value) {
            // if attribute is a flag
            if($value === null) {
                $str .= $key . ' ';
            }
            else {
                $str .= $key . '="' . $value . '" ';
            }
        }

        $str = trim($str);

        return $str;
    }


}