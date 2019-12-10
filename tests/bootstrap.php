<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;

define('DB_ENGINE', getenv('DB') ?: 'pgsql');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'orm_behaviors_test');
define('DB_USER', getenv('DB_USER') ?: null);
define('DB_PASSWD', getenv('DB_PASSWD') ?: null);

define('TESTS_PATH', __DIR__);
define('TESTS_TEMP_DIR', __DIR__ . '/temp');
define('VENDOR_PATH', realpath(__DIR__ . '/../vendor'));

AnnotationRegistry::registerFile(
    __DIR__ . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
);

$reader = new AnnotationReader();
$reader = new CachedReader($reader, new ArrayCache());
$_ENV['annotation_reader'] = $reader;
