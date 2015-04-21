<?php
/**
 * Class WpkScriptsCombine
 *
 * Provides an easy way to load and combine (and minify using the JShrink class) multiple scripts or stylesheets into a
 * single file that will be automatically included in the website.
 *
 * Author      wp.kytten
 * Author URI  https://github.com/wp-kitten
 * License     GPL v3
 * @version    1.0
 * @uses JSHrink\Minifier
 */
class WpkScriptsCombine
{
    /**
     * Indicates whether or not the '.js' file extension should be used for the output cache file
     * @type int
     */
    const TYPE_JS = 0;
    /**
     * Indicates whether or not the '.css' file extension should be used for the output cache file
     * @type int
     */
    const TYPE_CSS = 1;

    /**
     * Holds the system path to the cache directory
     * @see __construct()
     * @private
     * @var string
     */
    private $_cacheDirPath = '';
    /**
     * Holds the HTTP path to the cache directory
     * @see __construct()
     * @private
     * @var string
     */
    private $_cacheDirUrl = '';

    /**
     * Whether or not to minify the output. Defaults to true.
     * @see __construct()
     * @private
     * @var bool
     */
    private $_minify = true;

    /**
     * Class constructor
     *
     * Sets up the internal variables
     * @param bool $minify Whether or not to minify the output
     */
    function __construct($minify = true){
        $this->_cacheDirPath = trailingslashit(get_template_directory()).'wpk-scripts-combine/cache/';
        $this->_cacheDirUrl = trailingslashit(get_template_directory_uri()).'wpk-scripts-combine/cache/';

        if(! is_dir($this->_cacheDirPath)){
            trigger_error(__METHOD__."() Error: Directory {$this->_cacheDirPath} could not be found.", E_USER_ERROR);
        }
        if(! is_readable($this->_cacheDirPath) || !is_writable($this->_cacheDirPath)){
            trigger_error(__METHOD__."() Error: Directory {$this->_cacheDirPath} is not accessible.", E_USER_ERROR);
        }

        $this->_minify = $minify;
        if($minify){
            require_once(dirname(__FILE__).'/jshrink.php');
        }
    }

    /**
     * Combine and enqueue the specified list of scripts
     * @param array $files The list of files to combine
     * @param array $dependencies The list of dependencies. Ex: array('jquery', 'jquery-ui');
     * @param null  $baseDirPath The base directory path where to search for scripts.
     * @param bool  $inFooter Whether or not to include the output file in the page footer. Defaults to false.
     */
    public function combineScripts(array $files, array $dependencies = array(), $baseDirPath = null, $inFooter = false){
        if(!empty($files) && !empty($baseDirPath)){
            $scriptFilePath = $this->_cacheGet($files, self::TYPE_JS, $baseDirPath);
            wp_enqueue_script('wpk-'.rand(435,7365), $scriptFilePath, $dependencies, false, $inFooter);
        }
    }

    /**
     * Combine and enqueue the specified list of stylesheets
     * @param array $files The list of files to combine
     * @param array $dependencies The list of dependencies. Ex: array('main_css', 'template_css');
     * @param null  $baseDirPath The base directory path where to search for stylesheets.
     */
    public function combineStylesheets(array $files, array $dependencies = array(), $baseDirPath = null){
        if(!empty($files) && !empty($baseDirPath)){
            $scriptFilePath = $this->_cacheGet($files, self::TYPE_CSS, $baseDirPath);
            wp_enqueue_style('wpk-'.rand(8456,563246), $scriptFilePath, $dependencies);
        }
    }

    /**
     * Clear the cache directory
     *
     * @final
     * @public
     */
    final public function clearCache(){
        $exclude = array('.', '..', '.htaccess');
        foreach(new DirectoryIterator($this->_cacheDirPath) as $fileInfo){
            if(! in_array($fileInfo->getFilename(), $exclude)){
                @unlink($fileInfo->getRealPath());
            }
        }
    }

    /**
     * Create and retrieve the path to the cache file holding the combined scripts/stylesheets
     * @param array $files The list of scripts to combine
     * @param int $type The file extension to use for output file. 0 - js, 1 -css
     * @param int $baseDirPath The system path to the directory where to search for resources.
     * @return string
     */
    private function _cacheGet($files, $type = self::TYPE_JS, $baseDirPath = null)
    {
        if(empty($files) || empty($baseDirPath) || !is_dir($baseDirPath)){
            return ''; // 'empty data';
        }

        $baseDirPath = trailingslashit($baseDirPath);

        // Set the file type
        $fileType = ($type == self::TYPE_JS) ? '.js' : '.css';
        $closeScript = ($type == self::TYPE_JS) ? ';' : '';

        // Create the name of the cache file
        $cacheFnMD5 = md5(implode(';', $files));

        // Cache found
        $cacheFilePath = $this->_cacheDirPath.$cacheFnMD5.$fileType;
        $cacheFileUrl = $this->_cacheDirUrl.$cacheFnMD5.$fileType;
        if(is_file($cacheFilePath)){
            return $cacheFileUrl;
        }

        // Try to create the local cache file
        $fh = fopen($cacheFilePath, 'a+b');
        if(! $fh){
            return ''; // error: could not create cache file:
        }

        foreach($files as $file){
            $filePath = $baseDirPath.$file;
            if(empty($file) || !is_file($filePath)){
                // empty file or not a valid file path
                continue;
            }
            // If we have a valid path, get the content of the script file
            $content = file_get_contents($filePath);
            if(! empty($content)){
                fwrite($fh, '/*'.basename($filePath).'*/');
                if($this->_minify){
                    $content = JShrink\Minifier::minify($content);
                }
                $contentLength = strlen($content);
                // Check to see whether or not the script was closed properly
                if(! empty($closeScript)) {
                    if($content[$contentLength-1] != ';') {
                        $content .= $closeScript;
                        $contentLength++;
                    }
                }
                fwrite($fh, $content, $contentLength);
            }
        }
        fclose($fh);
        return $cacheFileUrl;
    }
}

