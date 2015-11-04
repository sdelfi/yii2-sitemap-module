<?php
use \assayerpro\sitemap\RobotsTxt;


class RobotsTxtTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testRobotsTxtHost()
    {
        $robotstxt = new RobotsTxt();
        $this->assertEquals('www.example.com', $robotstxt->host);
        $this->assertEquals("Host: www.example.com\n", $robotstxt->render());
    }
    public function testRobotsTxtHostHttps()
    {
        $_SERVER['HTTPS'] = 'on';
        $robotstxt = new RobotsTxt();
        $this->assertEquals('https://www.example.com', $robotstxt->host);
        $this->assertEquals("Host: https://www.example.com\n", $robotstxt->render());
    }
    public function testRobotsTxtSitemap()
    {
        $robotstxt = new RobotsTxt();
        $robotstxt->sitemap = 'http://www.example.com/sitemap.xml';
        $this->assertEquals("Host: www.example.com\nSitemap: http://www.example.com/sitemap.xml\n", $robotstxt->render());
    }
    public function testRobotsTxtCreateWithParams()
    {
        $_SERVER['HTTPS'] = 'on';
        $robotstxt = new RobotsTxt(['host' => 'example.net', 'sitemap' => 'http://example.net/data/sitemap.xml']);
        $this->assertEquals("Host: example.net\nSitemap: http://example.net/data/sitemap.xml\n", $robotstxt->render());
    }

}
