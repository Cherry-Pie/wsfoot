<?php

if (!function_exists('app_path')) {
    function app_path($path) {
        return __DIR__ .'/../'. $path;
    } // end app_path
}

