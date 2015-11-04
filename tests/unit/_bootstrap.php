<?php

$_SERVER["SERVER_NAME"] = 'www.example.com';
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require(__DIR__ . '../../../vendor/autoload.php');
require(__DIR__ . '../../../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', __DIR__);
Yii::setAlias('@webroot', __DIR__);

new \yii\console\Application([
    'id' => 'unit',
    'basePath' => __DIR__,
    'vendorPath' => __DIR__ . '/../../../vendor',
    'components' => [
        'request' => [
            'class' => '\yii\web\Request',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => '/main/default/index',
                '/api-v2' => '/api/version2/index',
            ],
            'baseUrl' => '',
            'hostInfo' => 'http://wwww.example.com/',
        ]
    ],

]);

