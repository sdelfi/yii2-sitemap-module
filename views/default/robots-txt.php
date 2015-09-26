<?php
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

