<?php

namespace Hizech\Bliss\ErrorManager;

use ErrorException;
use Throwable;

final class ErrorManager
{
    static private bool $display_errors = false;
    /** @var callable|null */
    static private $hook_callable = null;
    static private bool $handlers_registered = false;

    static public function init(
        bool $display_errors,
        ?callable $hook_callable,
    ): void {

        self::$display_errors = $display_errors;
        self::$hook_callable = $hook_callable;

    }

    /**
     * Register PHP error handlers. Safe to call multiple times (idempotent).
     * Handlers are registered once per process lifetime.
     */
    static public function handleErrors(): void
    {
        if (self::$handlers_registered) {
            return;
        }

        if(!is_string(ini_set('display_errors', '0'))) {
            echo 'Critical error, please refer to the administration.';
            exit;
        }
        error_reporting(E_ALL);

        set_exception_handler([ErrorManager::class, 'exceptionHandler']);

        set_error_handler(function ($level, $message, $file = '', $line = 0) : never
        {
            throw new ErrorException($message, 0, $level, $file, $line);
        });

        register_shutdown_function(function () : void
        {
            $error = error_get_last();
            if ($error !== null) {
                $e = new ErrorException(
                    $error['message'], 0, $error['type'], $error['file'], $error['line']
                );
                ErrorManager::exceptionHandler($e);
            }
        });

        self::$handlers_registered = true;
    }

    /**
     * Check if error handlers have been registered.
     */
    static public function isRegistered(): bool
    {
        return self::$handlers_registered;
    }

    static private function displayError(Throwable $error): void {
        echo '{| ' . $error . ' |}';
    }

    static public function exceptionHandler(Throwable $error): void {

        if(is_callable(self::$hook_callable)) call_user_func(self::$hook_callable, $error);

        if(self::$display_errors) self::displayError($error);

        exit(1);

    }

}
