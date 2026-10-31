<?php

declare(strict_types=1);

/**
 * Minimal stand-ins for the few CodeIgniter functions that some pure-testable helpers call.
 *
 * Each one is only defined when CodeIgniter is not loaded, so the same tests also pass in the suite
 * that boots the framework. Tests must therefore not depend on the exact host of base_url().
 */

if (!function_exists('base_url')) {
    function base_url(string $uri = ''): string
    {
        return 'http://test.local/' . ltrim($uri, '/');
    }
}

if (!function_exists('get_instance')) {
    /** The helpers do `$ci =& get_instance();`, so this returns by reference like the real one. */
    function &get_instance(): object
    {
        static $ci = null;
        $ci ??= new class {
            public object $load;

            public function __construct()
            {
                $this->load = new class {
                    public function helper(string $name): void
                    {
                    }
                };
            }
        };

        return $ci;
    }
}

if (!function_exists('write_file')) {
    function write_file(string $path, string $data, string $mode = 'wb'): bool
    {
        return @file_put_contents($path, $data) !== false;
    }
}
