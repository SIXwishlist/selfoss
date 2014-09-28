<?PHP

namespace helpers;

/**
 * Helper class for rendering template
 *
 * @package    helpers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class View {

    /**
     * current base url
     * @var string
     */
    public $base = '';

    /**
     * set global view vars
     */
    function __construct() {
        $this->genMinifiedJsAndCss();
        $this->base = $this->getBaseUrl();
    }

    
    /**
     * Returns the base url of the page. If a base url was configured in the 
     * config.ini this will be used. Otherwise base url will be generated by 
     * globale server variables ($_SERVER).
     */
    public function getBaseUrl() {
        $base = '';
        
        // base url in config.ini file
        if(strlen(trim(\F3::get('base_url')))>0) {
            $base = \F3::get('base_url');
            $length = strlen($base);
            if($length>0 && substr($base, $length-1, 1)!="/")
                $base .= '/';
                
        // auto generate base url
        } else {
            $lastSlash = strrpos($_SERVER['SCRIPT_NAME'], '/');
            $subdir = $lastSlash!==false ? substr($_SERVER['SCRIPT_NAME'], 0, $lastSlash) : '';
            
            $protocol = 'http';
            if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"]=="on" || $_SERVER["HTTPS"]==1))
                $protocol = 'https';
            
            $port = '';
            if (($protocol == 'http' && $_SERVER["SERVER_PORT"]!="80") ||
                ($protocol == 'https' && $_SERVER["SERVER_PORT"]!="443"))
                $port = ':' . $_SERVER["SERVER_PORT"];
            
            $base = $protocol . '://' . $_SERVER["SERVER_NAME"] . $port . $subdir . '/';
        }
        
        return $base;
    }
    
    
    /**
     * render template
     *
     * @return string rendered html
     * @param string $template file
     */
    public function render($template) {
        ob_start();
        include $template;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    
    
    /**
     * send error message
     *
     * @return void
     * @param string $message
     */
    public function error($message) {
        header("HTTP/1.0 400 Bad Request");
        die($message);
    }
    
    
    /**
     * send error message as json string
     *
     * @return void
     * @param mixed $datan
     */
    public function jsonError($data) {
        header('Content-type: application/json');
        $this->error( json_encode($data) );
    }
    
    
    /**
     * send success message as json string
     *
     * @return void
     * @param mixed $datan
     */
    public function jsonSuccess($data) {
        header('Content-type: application/json');
        die(json_encode($data));
    }
    
    
    
    /**
     * returns global JavaScript file name (all.js)
     *
     * @return string all.js file name
     */
    public static function getGlobalJsFileName() {
        return 'all-v' . \F3::get('version') . '.js';
    }
    
    
    /**
     * returns global CSS file name (all.css)
     *
     * @return string all.css file name
     */
    public static function getGlobalCssFileName() {
        return 'all-v' . \F3::get('version') . '.css';
    }
    
    
    
    /**
     * generate minified css and js
     *
     * @return void
     */
    public function genMinifiedJsAndCss() {
        // minify js
        $targetJs = \F3::get('BASEDIR').'/public/'.self::getGlobalJsFileName();
        if(!file_exists($targetJs) || \F3::get('DEBUG')!=0) {
            $js = "";
            foreach(\F3::get('js') as $file)
                $js = $js . "\n" . $this->minifyJs(file_get_contents(\F3::get('BASEDIR').'/'.$file));
            file_put_contents($targetJs, $js);
        }
    
        // minify css
        $targetCss = \F3::get('BASEDIR').'/public/'.self::getGlobalCssFileName();
        if(!file_exists($targetCss) || \F3::get('DEBUG')!=0) {
            $css = "";
            foreach(\F3::get('css') as $file)
                $css = $css . "\n" . $this->minifyCss(file_get_contents(\F3::get('BASEDIR').'/'.$file));
            file_put_contents($targetCss, $css);
        }
    }
    
    
    /**
     * minifies javascript if DEBUG mode is disabled
     *
     * @return minified javascript
     * @param javascript to minify
     */
    private function minifyJs($content) {
        if(\F3::get('DEBUG')!=0) 
            return $content;
        return \JSMin::minify($content);
    }
    
    
    /**
     * minifies css if DEBUG mode is disabled
     *
     * @return minified css
     * @param css to minify
     */
    private function minifyCss($content) {
        if(\F3::get('DEBUG')!=0) 
            return $content;
        return \CssMin::minify($content);
    }
    
}
