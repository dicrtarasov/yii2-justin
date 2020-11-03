<?php
/**
 * @author Igor A Tarasov <develop@dicr.org>
 * @version 27.07.20 08:02:23
 */

declare(strict_types = 1);

/**  */
define('YII_ENV', 'dev');
/**  */
define('YII_DEBUG', true);

require_once(dirname(__DIR__) . '/vendor/autoload.php');
require_once(dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php');

/** @noinspection PhpUnhandledExceptionInspection */
new yii\web\Application([
    'id' => 'test',
    'basePath' => dirname(__DIR__),
    'components' => [
        'cache' => [
            'class' => yii\caching\FileCache::class
        ],
        'log' => [
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                ]
            ]
        ],
    ],
    'modules' => [
        'justin' => [
            'class' => dicr\justin\JustinModule::class,
            'url' => dicr\justin\JustinModule::TEST_URL,
            'login' => dicr\justin\JustinModule::TEST_LOGIN,
            'passwd' => dicr\justin\JustinModule::TEST_PASSWD
        ]
    ],
    'bootstrap' => ['log']
]);
