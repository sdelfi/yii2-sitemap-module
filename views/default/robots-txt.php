<?php
/**
 * This view is used by sitemap/controllers/DefaultController.php
 * The following variables are available in this view:
 */
/* @var $host string the host
 *      $sitemap string the url for sitemap.xml
 */


echo "User-agent: *\n";
if (YII_DEBUG) {
    echo "# developer mode \n";
    echo "Disallow: /\n";
}

if (isset($host)) {
    echo 'Host: ', $host, "\n";
}
if (isset($sitemap)) {
    echo 'Sitemap: ', $sitemap, "\n";
}

