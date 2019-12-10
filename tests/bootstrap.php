<?php

declare(strict_types=1);

define('DB_ENGINE', getenv('DB') ?: 'pgsql');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'orm_behaviors_test');
define('DB_USER', getenv('DB_USER') ?: null);
define('DB_PASSWD', getenv('DB_PASSWD') ?: null);

define('TESTS_PATH', __DIR__);
define('TESTS_TEMP_DIR', __DIR__ . '/temp');
define('VENDOR_PATH', realpath(__DIR__ . '/../vendor'));

Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
    VENDOR_PATH . '/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
);

$reader = new \Doctrine\Common\Annotations\AnnotationReader();
$reader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());
$_ENV['annotation_reader'] = $reader;
