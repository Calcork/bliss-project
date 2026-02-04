<?php

namespace Hizech\Bliss\Route;

use ErrorException;
use Hizech\Bliss\Controller\ControllerHandler;
use Hizech\Bliss\Route\Parameter\Parameter;
use Hizech\Bliss\Route\Parameter\Type as ParameterType;

class Route
{
    /** @var string[] CommandCaller levels (segments) */
    public private(set) array $levels;

    /** @var array<int, HttpMethod> HTTP methods */
    public private(set) array $http_methods;

    /** @var array<string, Parameter> Parameters keyed by name */
    public private(set) array $parameters;

    /**
     * @param array<int, string> $levels
     * @param array<int, HttpMethod> $http_methods
     * @param array<string, \Hizech\Bliss\Route\Parameter\Parameter> $parameters
     */
    public function __construct(
        array $levels,
        public readonly ControllerHandler $controller_handler,
        array $http_methods,
        array $parameters = [],
    ) {
        // Validate levels: only allow a–z, 0–9, -, _
        foreach ($levels as $level) {
            if (!preg_match('/^[a-z0-9_-]+$/i', $level)) {
                throw new ErrorException("Invalid route level: '{$level}'");
            }
        }
        $this->levels = $levels;

        // Sort HTTP methods by value for consistency
        usort($http_methods, fn(HttpMethod $a, HttpMethod $b) => $a->value <=> $b->value);
        $this->http_methods = $http_methods;

        // Index parameters by name
        $this->parameters = [];
        foreach ($parameters as $parameter) {
            $this->parameters[$parameter->name] = $parameter;
        }
    }

    /** Build regex for matching the route */
    public function toRegex(): string
    {
        $levels_quoted = array_map(fn($segment) => preg_quote($segment, '#'), $this->levels);
        $base_path = count($levels_quoted) ? implode('/', $levels_quoted) : '';

        // Required params
        $required_pattern = '';
        foreach ($this->parameters as $parameter) {
            if ($parameter->type === ParameterType::Required) {
                $name = preg_quote($parameter->name, '#');
                $required_pattern .= '/' . $name . '/(?P<' . $parameter->name . '>[^/]+)';
            }
        }
        // If there are required params, the /r block must be present (to match toUri output)
        $required_block = $required_pattern !== '' ? '/r' . $required_pattern : '';

        // Optional params
        $optional_pattern = '';
        foreach ($this->parameters as $parameter) {
            if ($parameter->type === ParameterType::Optional) {
                $optional_pattern .= '(?:/' . preg_quote($parameter->name, '#') .
                                     '/(?P<' . $parameter->name . '>[^/]+))?';
            }
        }
        $optional_block = $optional_pattern !== '' ? '(?:/o' . $optional_pattern . ')?' : '';

        // Flag params
        $flag_pattern = '';
        foreach ($this->parameters as $parameter) {
            if ($parameter->type === ParameterType::Flag) {
                $flag_pattern .= '(?:/' . preg_quote($parameter->name, '#') . ')?';
            }
        }
        $flag_block = $flag_pattern !== '' ? '(?:/f' . $flag_pattern . ')?' : '';

        return '#^/' . $base_path . $required_block . $optional_block . $flag_block . '/?$#u';
    }

    /**
     * Build canonical URI from arguments
     * @param array<string, mixed> $args
     */
    public function toUri(array $args = []): string
    {
        $uri = '/' . implode('/', array_map('rawurlencode', $this->levels));

        // Required params
        $required_uri = '';
        foreach ($this->parameters as $parameter) {
            if ($parameter->type === ParameterType::Required) {
                if (!array_key_exists($parameter->name, $args)) {
                    throw new ErrorException("Missing required param '{$parameter->name}'");
                }
                $val = (string)$args[$parameter->name];
                if (!$parameter->validate($val)) {
                    throw new ErrorException("Param '{$parameter->name}' failed validation");
                }
                $required_uri .= '/' . rawurlencode($parameter->name) . '/' . rawurlencode($val);
            }
        }
        if ($required_uri !== '') {
            $uri .= '/r' . $required_uri;
        }

        // Optional params
        $optional_uri = '';
        foreach ($this->parameters as $parameter) {
            if ($parameter->type === ParameterType::Optional && array_key_exists($parameter->name, $args)) {
                $val = (string)$args[$parameter->name];
                if (!$parameter->validate($val)) {
                    throw new ErrorException("Param '{$parameter->name}' failed validation");
                }
                $optional_uri .= '/' . rawurlencode($parameter->name) . '/' . rawurlencode($val);
            }
        }
        if ($optional_uri !== '') {
            $uri .= '/o' . $optional_uri;
        }

        // Flags
        $flags = [];
        foreach ($this->parameters as $parameter) {
            if ($parameter->type === ParameterType::Flag && !empty($args[$parameter->name])) {
                $flags[] = $parameter->name;
            }
        }
        sort($flags, SORT_STRING);
        $flag_uri = '';
        foreach ($flags as $flag) {
            $flag_uri .= '/' . rawurlencode($flag);
        }
        if ($flag_uri !== '') {
            $uri .= '/f' . $flag_uri;
        }

        return $uri;
    }
}
