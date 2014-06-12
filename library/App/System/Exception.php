<?php

class App_System_Exception extends Zend_Exception
{

    /**
     * Error handling
     *
     * @param exception $exception
     */
    public static function catchException(Exception $exception)
    {
    	header("HTTP/1.1 301 Moved Permanently");
    	header("Location: http://".$_SERVER['HTTP_HOST']);
    	die();
    	
    	$message = $exception->getMessage();
        $trace = $exception->getTraceAsString();
        $str = 'ERROR: ' . $message . "\n" . $trace;
        $config = Zend_Registry::get('config');
        if($config->debug->on) {
            echo "<pre>";
            print_r($str);
            echo "</pre>";
        }
        else {
        	$view = new Zend_View();
        	$path_to_scripts = Zend_Registry::get('config')->path->layouts;
        	if(strlen($path_to_scripts)>0 && file_exists($path_to_scripts.'error.tpl')) {
        		$view->setScriptPath($path_to_scripts);
				$view->Error = "System error! Please try later";
				echo $view->render('error.tpl');
        	} else
        		Zend_Debug::dump('System error! Please try later');
        }
    }
}
