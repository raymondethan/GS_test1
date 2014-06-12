<?php
 	$config = array(
 		'db'    => array (
        	'adapter'   => 'PDO_MYSQL',
        	'params'    => array(
            	'host'          => '127.0.0.1',
            	'username'      => 'globalstories',
            	'password'      => '9WDfu8CzbuRRustC',
            	'dbname'        => 'globalstories_v2',
            	'driver_options'=> array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'),
            	'profiler'      => false,
        	),
        	'active' => true
    	),
		'path' => array(
        	'root'         => ROOT_PATH. DS,
        	'structure'	   => ROOT_PATH. DS. 'public'.DS.'structure'.DS,
        	'application'  => ROOT_PATH .DS. 'application'.DS,
        	'library'    => ROOT_PATH .DS. 'library'.DS,
        	'helpers'      => ROOT_PATH .DS. 'application'.DS.'libraries'.DS.'App'.DS.'Helper',
        	'controllers'  =>
        		array(
        			'default' => ROOT_PATH .DS. 'application'.DS.'modules'.DS.'default'.DS.'controllers'.DS,
        		),
        	'models'       => ROOT_PATH .DS. 'application'.DS.'models'.DS,
        	'views'        => ROOT_PATH .DS. 'application'.DS.'views'.DS,
        	'layouts'      => ROOT_PATH .DS. 'application'.DS.'layouts'.DS,
        	'config'       => ROOT_PATH .DS. 'application'.DS.'config'.DS,
        	'system'	   => ROOT_PATH .DS. 'application'.DS.'system'.DS,
        	'languages'    => ROOT_PATH .DS. 'application'.DS.'languages'.DS,
        	'products'	   => ROOT_PATH .DS. 'public' .DS. 'img'.DS.'products'.DS,
        	'offline'	   => ROOT_PATH .DS. 'offline' .DS,
        	'uploads'	   => ROOT_PATH .DS. 'public' .DS. 'uploads'.DS	
 		),
 		'url'   => array (
         	'base_dir'         => 'public/',
         	'images'          => 'images/',
         	'css'          => 'css/',
         	'js'          => 'js/',
 			'flash'          => 'flash/',
     	),
     	'debug' => array (
			'on' => true
		),
        'api_domain' => 'api.development.globalstories.org',
        'api_response_format' => 'xml',
        'session' => array(
        		'name'           => 'Session',
				'primary'        => 'id',
				'modifiedColumn' => 'modified',
				'dataColumn'     => 'data',
				'lifetimeColumn' => 'lifetime'
                )
 	);
?>
