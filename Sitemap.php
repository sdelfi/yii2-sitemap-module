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

    /** @var int */
    public $cacheExpire = 86400;

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
    public function render()
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
            if (isset($item['images'])) {
               $item['images'] = array_map(function ($image) {
                   $image['loc'] = Url::to($image['loc'], true);
                   return $image;
               }, $item['images']);
            }
            return $item;
        }, $urls);

        $dom = new \DOMDocument('1.0', 'utf-8');
        $urlset = $dom->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlset->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
        $urlset->setAttribute('xmlns:news', 'http://www.google.com/schemas/sitemap-news/0.9');
        foreach ($urls as $urlItem) {
            $url = $dom->createElement('url');
            foreach ($urlItem as $urlKey => $urlValue) {
                if (is_array($urlValue)) {
                    switch ($urlKey) {
                        case 'news':
                            $nameSpace = 'news:';
                            $elem = $dom->createElement($nameSpace.$urlKey);
                            foreach ($urlValue as $subKey => $subValue) {
                                $subElem = $dom->createElement($nameSpace.$subKey);
                                if (is_array($subValue)){
                                    foreach ($subValue as $sub2Key => $sub2Value) {
                                        $sub2Elem = $dom->createElement($nameSpace.$sub2Key);
                                        $sub2Elem->appendChild($dom->createTextNode($sub2Value));
                                        $subElem->appendChild($sub2Elem);
                                    }

                                } else {
                                    $subElem->appendChild($dom->createTextNode($subValue));
                                }
                                $elem->appendChild($subElem);
                            }
                            $url->appendChild($elem);
                            break;
                        case 'images':
                            $nameSpace = 'image:';
                            foreach ($urlValue as $image) {
                                $elem = $dom->createElement($nameSpace.'image');
                                foreach ($image as $imgKey => $imgValue) {
                                    $img = $dom->createElement($nameSpace.$imgKey);
                                    $img->appendChild($dom->createTextNode($imgValue));
                                    $elem->appendChild($img);
                                }
                                $url->appendChild($elem);
                            }
                            break;
                    }
                } else {
                    $elem = $dom->createElement($urlKey);
                    $elem->appendChild($dom->createTextNode($urlValue));
                    $url->appendChild($elem);
                }
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
