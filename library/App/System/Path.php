<?php
/**
 * Path class file
 *
 * @see VmPath
 *
 * @package Vm
 * @author Anton Suprun <anton.suprun@voltimum.com>
 * @version 1.0
 */

/**
 * Path class
 *
 * This class detects and stores all the required path variables, like the
 * script root path or site URL. See {@link VmPath::get()} for a complete list.
 *
 * <b>NOTE:</b> an instance of this class is assigned to each template as a $p
 * variable.
 *
 * @package Vm
 * @author Anton Suprun <anton.suprun@voltimum.com>
 * @version 1.0
 */
class App_System_Path
{
    /**
     * Path to all media, incl. images, css, etc.
     * @var string
     */
    public $media;

    /**
     * Absolute site URL
     * @var string
     */
    public $site;

    /**
     * Absolute current element URL
     * @var string
     */
    public $item;

    /**
     * Get certain path
     *
     * Path type can be one of the following:
     * - root:    absolute path to script root, with trailing slash
     * - site:    (default) real part of site URL, like http://example.com/
     * - request: virtual part of site URL, like projects/philips, no trailing slash
     * - address: same as request, only stored as an array
     * - item:    complete path, with trailing slash
     *
     * @param string $type
     *
     * @return string
     */
    public static function get($type = 'site')
    {
        static $path = null;

        if ($path === null)
        {
            $path['root'] = realpath(getcwd()) . DS;

            // Cache site URL
            $path['site']  = $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
            $path['site'] .= $_SERVER['HTTP_HOST'];
            $path['base'] = $path['site'];

            // Cache request
            $path['address'] = array();

            if (isset($_GET['request']))
            {
                $request = explode('/', $_GET['request']);

                foreach ($request as $r)
                {
                    if (strlen($r) > 0) {
                        $path['address'][] = $r;
                    }
                }
            }

            // Cache properly cleaned request string
            $path['request'] = implode('/', $path['address']);

            $subdir = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $subdir = preg_replace('#/+#', '/', $subdir);
            $subdir = str_replace($path['request'], '', $subdir);
            $subdir = preg_replace('#/+$#', '/', $subdir);

            // Cache site global path
            $path['site'] .= $subdir;

            // Cache current item path
            $path['item'] = $path['site'] . $path['request'] . ($path['request'] != '' ? '/' : '');

            // If we have any query, process and use it
            $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

            if ($query)
            {
                $array = array();

                parse_str($query, $array);

                foreach ($array as $k => $v) {
                    $_GET[$k] = $v;
                }
            }
        }

        return $path[$type];
    }

    /**
     * Return an instance
     *
     * @staticvar VmPath $instance an instance of the class
     *
     * @return VmPath
     */
    public static function getInstance()
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new App_System_Path;
        }

        return $instance;
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->site  = self::get('site');
        $this->item  = self::get('item');
    }
}
?>
