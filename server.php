<?php
//	php -S localhost:8888 server.php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|php)$/', $_SERVER["REQUEST_URI"])) {
    return false;
} else {
    include __DIR__ . '/index.php';
}