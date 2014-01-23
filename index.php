<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/goutte.phar';

use Goutte\Client;
use BOL\BOLCrawler;
use BOL\BOLBook;

$container = new \Pimple();

require __DIR__.'/app/config.php';

// BEGIN HACK to avoid DateTime PHP warnings
date_default_timezone_set('Europe/Brussels');
// END HACK to avoid DateTime PHP warnings

// Create a Book
$container['book'] = $container->share(function(Pimple $container) {
    return new BOLBook(
        $container['book.title'],
        $container['book.begin'],
        $container['book.end']
    );
});

// Create Reader service
$container['reader'] = $container->share(function(Pimple $container) {
    return new BOLCrawler($container['book']->getTitle());
});

// Create Client service
$container['client'] = $container->share(function() {
    return new Client();
});

// Read the Book
$container['book']->read($container['reader'], $container['client']);