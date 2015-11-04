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
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
                'duration' => Yii::$app->sitemap->cacheExpire,
                'variations' => [ Yii::$app->request->get('id')],
            ],
        ];
    }

    /**
     * Action for sitemap/default/index
     *
     * @access public
     * @return string
     */
    public function actionIndex($id=0)
    {
        $sitemap = Yii::$app->sitemap->render();
        if (empty($sitemap[$id])) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/xml');
        if (Yii::$app->sitemap->enableGzip) {
            $sitemap = gzencode($sitemap);
            $headers->add('Content-Encoding', 'gzip');
            $headers->add('Content-Length', strlen($sitemap));
        }
        return $sitemap[$id]['xml'];
    }

    /**
     * Action for sitemap/default/robot-txt
     *
     * @access public
     * @return string
     */
    public function actionRobotsTxt()
    {
        $robotsTxt = Yii::$app->robotsTxt;
        $robotsTxt->sitemap = Yii::$app->urlManager->createAbsoluteUrl([$this->module->id.'/'. $this->id .'/index']);
        Yii::$app->response->format = 'txt';
        return $robotsTxt->render();
    }
}
