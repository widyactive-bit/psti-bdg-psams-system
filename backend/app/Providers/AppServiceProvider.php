<?php

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

namespace App\Providers {
    use Illuminate\Support\ServiceProvider;

    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            //
        }

        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            //
        }
    }
}
