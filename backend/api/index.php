<?php

// Fix Laravel routing base path on Vercel
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

// Forward request to Laravel's index.php
require __DIR__ . '/../public/index.php';
