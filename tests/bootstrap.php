<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 12.07.20 20:42:35
 */

declare(strict_types = 1);

define('YII_ENV', 'dev');
define('YII_DEBUG', true);

require_once(dirname(__DIR__) . '/vendor/autoload.php');
require_once(dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php');

/** @noinspection PhpUnhandledExceptionInspection */
new yii\web\Application([
    'id' => 'test',
    'basePath' => __DIR__,
    'components' => [
        'cache' => yii\caching\ArrayCache::class,
        'justin' => [
            'class' => dicr\justin\JustinApi::class,
            'test' => true
        ]
    ]
]);
