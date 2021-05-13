<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 14.05.21 04:41:10
 */

declare(strict_types = 1);

/**  */
const YII_ENV = 'dev';
/**  */
const YII_DEBUG = true;

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
                    'levels' => ['error', 'warning', 'info', 'trace']
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
