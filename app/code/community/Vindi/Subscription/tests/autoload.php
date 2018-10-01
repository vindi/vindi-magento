<?php

require_once 'vendor/autoload.php';

if (file_exists('vendor/vlucas/phpdotenv/src/Dotenv.php')) {
    (new Dotenv\Dotenv(__DIR__))->load();
}

