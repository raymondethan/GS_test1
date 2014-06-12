<?php
// For our dev environment we will report all errors to the screen    
error_reporting(E_ALL | E_STRICT);    
ini_set('display_startup_errors', 1);    
ini_set('display_errors', 1);


// Set our timezone
date_default_timezone_set('Europe/Sofia');   


// Add /library and /application directory to our include path 
$siteRootDir = dirname(__FILE__).'/../';

set_include_path(
    $siteRootDir . '/library' . PATH_SEPARATOR 
    . $siteRootDir . '/application' . PATH_SEPARATOR
    . $siteRootDir . '/application/models' . PATH_SEPARATOR 
    . get_include_path()
);


// Turn on autoloading, so we do not include each Zend Framework class
require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();


// Create registry object and setting it as the static instance in the Zend_Registry class
$registry = new Zend_Registry();
Zend_Registry::setInstance($registry);

//save $siteRootDir in registry:
$registry->set('siteRootDir', $siteRootDir);

// Load configuration file and store the data in the registry
$configuration = new Zend_Config_Ini($siteRootDir . '/configuration/config.ini', 'main');
Zend_Registry::set('configuration', $configuration);

// Construct the database adapter class, connect to the database and store the db object in the registry
$db = Zend_Db::factory($configuration->db);
$db->query("SET NAMES 'utf8'");
Zend_Registry::set('db', $db);
// set this adapter as default for use with Zend_Db_Table
Zend_Db_Table_Abstract::setDefaultAdapter($db);

// Setup the Front Controller, disable the error handler, set our controller directories 
$frontController = Zend_Controller_Front::getInstance();   
$frontController->throwExceptions(true);   
$frontController->addModuleDirectory($siteRootDir . '/application/modules');
//we want the front controller to return the response, instead of emitting it automatically
$frontController->returnResponse(true);
// Definig Routers

include $siteRootDir . '/configuration/routes.php';
$frontController->setRouter($router);


//$activateUserRoute = new Zend_Controller_Router_Route('user/activate',
    //array('controller'=>'user', 'action'=>'activate')
    //);
//$router->addRoute('activateUser', $activateUserRoute);
/* We want to set the encoding to UTF-8, so we won't rely on the ViewRenderer action helper by default,
 * but will construct view object and deliver it to the ViewRenderer after setting some options. 
 */ 
//$view = new Zend_View(array('encoding'=>'UTF-8'));
//$view->addHelperPath($siteRootDir . '/library/My/View/Helper', 'My_View_Helper');
//$view->addHelperPath('My/View/Helper', 'My_View_Helper_');
//$viewRendered = new Zend_Controller_Action_Helper_ViewRenderer($view);
//Zend_Controller_Action_HelperBroker::addHelper($viewRendered);
//Zend_Controller_Action_HelperBroker::addPath('My/Controller/Action/Helper', 'My_Controller_Action_Helper');
//$authUsersHelper = new My_Controller_Action_Helper_AuthUsers();
//Zend_Controller_Action_HelperBroker::addHelper($authUsersHelper);


// Now we initialize the Zend_Layout object with MVC support
Zend_Layout::startMvc(
    array(
        'layoutPath' => $siteRootDir . '/application/layouts',
        'layout' => 'main'
    )
);


// run the dispatch, get the response and send it to the client   
$response = $frontController->dispatch();
$response->sendResponse();

function __($string)
{
    return $string;
}

