<?php

// Polyfill for missing PHP intl extension and NumberFormatter class in local dev
namespace Illuminate\Support {
    if (!function_exists('Illuminate\Support\extension_loaded')) {
        function extension_loaded($name) {
            if ($name === 'intl') {
                return true;
            }
            return \extension_loaded($name);
        }
    }
}

namespace {
    if (!class_exists('NumberFormatter')) {
        class NumberFormatter {
            public const DECIMAL = 1;
            public const SPELLOUT = 2;
            public const PERCENT = 3;
            public const CURRENCY = 4;
            public const ORDINAL = 5;
            public const TYPE_DEFAULT = 0;
            public const TYPE_INT32 = 1;
            public const TYPE_INT64 = 2;
            public const TYPE_DOUBLE = 3;
            public const MAX_FRACTION_DIGITS = 0;
            public const FRACTION_DIGITS = 1;
            public const DEFAULT_RULESET = 2;

            private $locale;
            private $style;

            public function __construct($locale, $style, $pattern = null) {
                $this->locale = $locale;
                $this->style = $style;
            }

            public function format($value, $type = null) {
                return number_format($value);
            }

            public function formatCurrency($value, $currency) {
                return $currency . ' ' . number_format($value, 2);
            }

            public function setAttribute($attr, $value) {}
            public function setSymbol($symbol, $value) {}
            public function setTextAttribute($attr, $value) {}
            
            public function parse($string, $type = null, &$position = null) {
                return floatval($string);
            }
        }
    }
}

// Laravel Application Bootstrapping
namespace {
    use Illuminate\Foundation\Application;
    use Illuminate\Foundation\Configuration\Exceptions;
    use Illuminate\Foundation\Configuration\Middleware;
    use Illuminate\Http\Request;

    $app = Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            api: __DIR__.'/../routes/api.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware): void {
            $middleware->trustProxies(at: '*');
        })
        ->withExceptions(function (Exceptions $exceptions): void {
            $exceptions->shouldRenderJsonWhen(
                fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
            );
        })->create();

    if (env('VERCEL')) {
        $app->useStoragePath('/tmp/storage');
        $storagePaths = [
            '/tmp/storage',
            '/tmp/storage/app',
            '/tmp/storage/app/public',
            '/tmp/storage/framework',
            '/tmp/storage/framework/views',
            '/tmp/storage/framework/cache',
            '/tmp/storage/framework/sessions',
            '/tmp/storage/bootstrap',
            '/tmp/storage/bootstrap/cache',
        ];
        foreach ($storagePaths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    return $app;
}
