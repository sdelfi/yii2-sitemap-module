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
    const SCHEMAS = [
        'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
        'xmlns:image' => 'http://www.google.com/schemas/sitemap-image/1.1',
        'xmlns:news' => 'http://www.google.com/schemas/sitemap-news/0.9',
    ];

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
        $urls = $this->generateUrls();
        $dom = new \DOMDocument('1.0', Yii::$app->charset);
        $urlset = $dom->createElement('urlset');
        foreach (static::SCHEMAS as $attr => $schemaUrl) {
            $urlset->setAttribute($attr, $schemaUrl);
        }
        foreach ($urls as $urlItem) {
            $url = $dom->createElement('url');
            foreach ($urlItem as $urlKey => $urlValue) {
                if (is_array($urlValue)) {
                    switch ($urlKey) {
                        case 'news':
                            $namespace = 'news:';
                            $elem = $dom->createElement($namespace.$urlKey);
                            $url->appendChild(static::hashToXML($urlValue, $elem, $dom, $namespace));
                            break;
                        case 'images':
                            $namespace = 'image:';
                            foreach ($urlValue as $image) {
                                $elem = $dom->createElement($namespace.'image');
                                $url->appendChild(static::hashToXML($image, $elem, $dom, $namespace));
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
     * Generate url's array from properties $url and $models
     *
     * @access protected
     * @return array
     */
    protected function generateUrls()
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

        return $urls;
    }

    /**
     * Convert associative arrays to XML
     *
     * @param array $hash
     * @param \DOMElement $node
     * @param \DOMDocument $dom
     * @param string $namespace
     * @static
     * @access protected
     * @return \DOMElement
     */
    protected static function hashToXML($hash, $node, $dom, $namespace = '')
    {
        foreach ($hash as $key => $value) {
            $elem = $dom->createElement($namespace.$key);
            if (is_array($value)) {
                $elem = static::hashToXML($value, $elem, $dom, $namespace);
            } else {
                $elem->appendChild($dom->createTextNode($value));
            }
            $node->appendChild($elem);
        }
        return $node;
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
