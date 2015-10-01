<?php
/**
 * DefaultController for sitemap module
 *
 * @link https://github.com/himiklab/yii2-sitemap-module
 * @author Serge Larin <serge.larin@gmail.com>
 * @author HimikLab
 * @copyright 2015 Assayer Pro Company
 * @copyright Copyright (c) 2014 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace assayerpro\sitemap\controllers;

use Yii;
use yii\web\Controller;

/**
 * DefaultController for sitemap module
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @author HimikLab
 * @package assayerpro\sitemap
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'pageCache' => [
                'class' => 'yii\filters\PageCache',
                'only' => ['index', 'robots-txt'],
                'duration' => \Yii::$app->sitemap->cacheExpire,
            ],
        ];
    }

    /**
     * Action for sitemap/default/index
     *
     * @access public
     * @return string
     */
    public function actionIndex()
    {
        $sitemap = Yii::$app->sitemap->render();

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/xml');
        if (Yii::$app->sitemap->enableGzip) {
            $sitemap = gzencode($sitemap);
            $headers->add('Content-Encoding', 'gzip');
            $headers->add('Content-Length', strlen($sitemap));
        }
        return $sitemap;
    }

    /**
     * Action for sitemap/default/robot-txt
     *
     * @access public
     * @return string
     */
    public function actionRobotsTxt()
    {
        \Yii::$app->response->format = 'txt';
        return $this->renderPartial('robots-txt', [
            'host' => \Yii::$app->request->serverName,
            'sitemap' => \Yii::$app->urlManager->createAbsoluteUrl([$this->module->id.'/'. $this->id .'/index']),
        ]);
    }
}
