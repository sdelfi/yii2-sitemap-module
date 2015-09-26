<?php
/**
 * @link https://github.com/himiklab/yii2-sitemap-module
 * @copyright Copyright (c) 2014 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace assayerpro\sitemap;

use Yii;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\helpers\Url;

/**
 * Yii2 module for automatically generating XML Sitemap.
 *
 * @author HimikLab
 * @package himiklab\sitemap
 */
class Sitemap extends \yii\base\Component
{
    const ALWAYS = 'always';
    const HOURLY = 'hourly';
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';
    const YEARLY = 'yearly';
    const NEVER = 'never';

    public $controllerNamespace = 'himiklab\sitemap\controllers';

    /** @var int */
    public $cacheExpire = 86400;

    /** @var Cache|string */
    public $cacheProvider = 'cache';

    /** @var string */
    public $cacheKey = 'sitemap';

    /** @var boolean Use php's gzip compressing. */
    public $enableGzip = false;

    /** @var array */
    public $models = [];

    /** @var array */
    public $urls = [];

    /**
     * Build site map.
     * @return string
     */
    public function buildSitemap()
    {
        $urls = $this->urls;

        foreach ($this->models as $modelName) {
            /** @var behaviors\SitemapBehavior $model */
            if (is_array($modelName)) {
                $model = new $modelName['class'];
                if (isset($modelName['behaviors'])) {
                    $model->attachBehaviors($modelName['behaviors']);
                }
            } else {
                $model = new $modelName;
            }
            $urls = array_merge($urls, $model->generateSiteMap());
        }
        $urls = array_map(function ($item) {
            $item['loc'] = Url::to($item['loc'], true);
            if (isset($item['lastmod'])) {
                $item['lastmod'] = Sitemap::dateToW3C($item['lastmod']);
            }
            return $item;
        }, $urls);

        $dom = new \DOMDocument('1.0', 'utf-8');
        $urlset = $dom->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($urls as $urlItem) {
            $url = $dom->createElement('url');
            foreach ($urlItem as $key => $value) {
                $elem = $dom->createElement($key);
                $elem->appendChild($dom->createTextNode($value));
                $url->appendChild($elem);
            }
            $urlset->appendChild($url);
        }
        $dom->appendChild($urlset);
        return $dom->saveXML();
    }

    /**
     * Convert date to W3C format
     *
     * @param mixed $date
     * @static
     * @access protected
     * @return string
     */
    public static function dateToW3C($date)
    {
        if (is_int($date)) {
            return date(DATE_W3C, $date);
        } else {
            return date(DATE_W3C, strtotime($date));
        }
    }

}
