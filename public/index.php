<?php

if (get_magic_quotes_gpc()) {
    function stripslashes_gpc(&$value)
    {
        $value = stripslashes($value);
    }
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

    define ('DS', DIRECTORY_SEPARATOR);
    $current_path = realpath (dirname (__FILE__));
    $path_parts = explode (DS, $current_path);
    array_splice ($path_parts, count($path_parts)-1, 1);
    $base_path = implode (DS, $path_parts);
    define ('ROOT_PATH', $base_path);
	
    require ROOT_PATH.DS.'application'.DS.'config'.DS.'countries.php';
    require ROOT_PATH.DS.'application'.DS.'config'.DS.$config_file;

    $paths = implode(PATH_SEPARATOR,
                    array (
                    	$config['path']['library'],
                    	$config['path']['system'],
                    	$config['path']['models']
                    ));
    set_include_path($paths);

    require 'InitApplication.php';
    require 'Debug.php';

    $boot = new InitApplication();
    $boot->run($config);