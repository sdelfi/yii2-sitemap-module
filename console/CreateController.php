<?php
/**
 * CreateController for sitemap module
 *
 * @link https://github.com/assayer-pro/yii2-sitemap-module
 * @author Serge Larin <serge.larin@gmail.com>
 * @copyright 2015 Assayer Pro Company
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace assayerpro\sitemap\console;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;


/**
 * Generate sitemap for application
 *
 * @author Serge Larin <serge.larin@gmail.com>
 * @package assayerpro\sitemap
 */
class CreateController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'create';
    /**
     * Generate sitemap.xml file
     *
     * @access public
     * @return string
     */
    public function actionCreate($file='@webroot/sitemap.xml')
    {
        $file = Yii::getAlias($file);
        $this->stdout("Generate sitemap file.\n", Console::FG_GREEN);
        $this->stdout("Rendering sitemap...\n", Console::FG_GREEN);
        $sitemap = Yii::$app->sitemap->render();

        $this->stdout("Writing sitemap to $file\n", Console::FG_GREEN);
        file_put_contents($file, $sitemap[0]['xml']);
        for ($i=1; $i < count($sitemap); $i++) {
            $file = Yii::getAlias('@webroot' . $sitemap[$i]['file']);
            $this->stdout("Writing sitemap to $file\n", Console::FG_GREEN);
            file_put_contents($file, $sitemap[$i]['xml']);
        }
        $this->stdout("Done\n", Console::FG_GREEN);
        return self::EXIT_CODE_NORMAL;
    }
}
