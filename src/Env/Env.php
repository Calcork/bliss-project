<?php

namespace Hizech\Bliss\Env;

use Hizech\Bliss\Data\Variable;
use Hizech\Bliss\Misc\Util;
use ErrorException;

class Env
{

    /**
     * @param array<string, int|bool|float|string|null> $ar
     * @return string
     */
    static function envToFileContent(array $ar): string
    {
        $str = '';

        foreach ($ar as $key => $value) {
            $s = Variable::inverseCastStringyQuery($key, $value);
            $str .= $s . PHP_EOL;
        }

        return $str;
    }

    /**
     * @param string $content
     * @return array<string, string>
     */
    static private function contentToEnv(string $content): array
    {

        $regex = Variable::STRINGY_QUERY_REGEX;

        preg_match_all($regex, $content, $matches, PREG_SET_ORDER, 0);

        $vars = [];

        foreach ($matches as $m) {

            $var_array = Variable::castStringyQuery($m[0]);
            $vars[$var_array['name']] = $var_array['value'];

        }

        return $vars;

    }

    /**
     * @return array<string, string>
     */
    static function fileToEnv(string $env_path): array
    {

        if (!is_file($env_path)) {
            throw new ErrorException("Env file not found: {$env_path}");
        }

        $content = file_get_contents($env_path);

        $env = self::contentToEnv($content);

        return $env;

    }

}