<?php

require 'Zend/Loader/Autoloader.php';

class InitApplication
{

	/**
	 * Configuration Object
	 *
	 * @var Zend_Config
	 */
	private $_config = null;

	/**
	 * Run application
	 *
	 * @var array $config Configuration
	 */
	public function run($config)
	{
		try
		{
			//$this->setDebugger();
			$this->setLoader();
			$this->setConfig($config);
			$this->setView();
			$this->setDbAdapter();
			//$this->setParams();
			//$this->setLogger();
			$this->setSession();
			$this->setUser();

			
			$router = $this->setRouter();
			$front = Zend_Controller_Front::getInstance();
			$front->setBaseUrl($this->_config->url->base)
					->throwexceptions(true)
					->setRouter($router)
					//->registerPlugin(new App_Controller_Plugin_FlashMessenger())
					;

			Zend_Controller_Front::run($this->_config->path->controllers->toArray());
		}
		catch (Exception $e)
		{
			App_System_Exception::catchException($e);
		}
	}
	
	public function setSession()
	{
            $handler = new Zend_Session_SaveHandler_DbTable($this->_config->session->toArray());
            Zend_Session::setSaveHandler($handler);
            Zend_Session::start();
	}
	
	/**
	 * Set Loaders
	 */
	public function setLoader()
	{
		$loader = Zend_Loader_Autoloader::getInstance();
		$loader->registerNamespace('App_');
	}

	/**
	 * Set config
	 *
	 * @param array $config Settings
	 */
	public function setConfig($config)
	{
		$config = new Zend_Config($config);
		$this->_config = $config;
                $this->_mode = ($this->_config->api_domain == $_SERVER['HTTP_HOST']) ? 'api' : 'default';
                Zend_Registry::set('mode', $this->_mode); 
		Zend_Registry::set('config', $config); 

	}
	

	/**
	 * Set View
	 */
	public function setView()
	{
		Zend_Layout::startMvc(array(
					'layoutPath' => $this->_config->path->layouts,
					'layout' => 'main',
				));

		$layout = Zend_Layout::getMvcInstance();

		$view = $layout->getView();

		$layout->setViewSuffix('phtml');

//             $view->baseUrl = $this->_config->url->base;
//			 $view->cssUrl = App_System_Path::get().$this->_config->url->css;
//			 $view->jsUrl = App_System_Path::get().$this->_config->url->js;
//			 $view->imgUrl = App_System_Path::get().$this->_config->url->img;

		$view->setBasePath($this->_config->path->views);
		$view->addScriptPath($this->_config->path->layouts);
//			 $view->addHelperPath($this->_config->path->helpers, 'App_Helper');
		$layout->setView($view);
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer
				->setView($view)
				->setViewSuffix('phtml');

		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
	}

	/**
	 * Set Database Connection and put it to registry
	 */
	public function setDbAdapter()
	{
		if ($this->_config->db->active)
		{
			$db = Zend_Db::factory($this->_config->db);
			Zend_Db_Table_Abstract::setDefaultAdapter($db);
			Zend_Registry::set('db', $db);
		}
	}

	public function setParams()
	{
		@Zend_Loader::loadClass('Config_Model');
		$config_model = new Config_Model();
		Zend_Registry::set('params', $config_model->getParams());
	}

	public function setUser()
	{
		$identity = Zend_Auth::getInstance()->getIdentity();
		if (!is_null($identity))
		{
			/*$db = Zend_Registry::get('db');
			$select = $db->select()->from('user_roles');
			$select->where('id = (?)', $identity->role_id);
			$result = $db->fetchRow($select);
			$identity->role = $result['name'];*/
		}
		Zend_Registry::set('identity', $identity);
	}

        protected function setDebugger()
        {
            if( $_REQUEST )
            {
                $params = explode('/', $_REQUEST['request']);
                foreach($params as $key => $value)
                {
                    if($value == 'debug' && $params[++$key] == 'true')
                    {
                        Debug::getInstance()->setDebugging(true);
                    }
                }
            }
        }

	protected function setLogger()
	{
	    if(Zend_Registry::get('params')->application->log_user_actions > 0)
            {
	    	$front = Zend_Controller_Front::getInstance();
    		$front->setRequest(new Zend_Controller_Request_Http());

                $request = $front->getRequest();
                
                $uid = Zend_Registry::isRegistered('identity') ? Zend_Registry::get('identity')->uid : 0;
                $type = ($request->isPost()) ? 'POST' : 'GET';

    		$log_array = array(	'user_id' => (!is_null($uid)) ? $uid : 0,
    							'url' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
    						   	'time' => date("Y-m-d H:i:s",time()),
    							'type' => $type,
    							'data' => serialize($request->getParams())
    						  );
    		
    		$db = Zend_Registry::get('db');
    		$db_logger = new Zend_Log_Writer_Db($db, Zend_Registry::get('config')->db->table_pfx.'_log');
    		$db_logger->write($log_array);
            }
	}

	/**
	 * Set Routes
	 */
	public function setRouter()
	{
		require($this->_config->path->config . 'routes.php');
		if (!($router instanceof Zend_Controller_Router_Abstract))
		{
			throw new Exception('Incorrect config file: routes');
		}
		return $router;
	}

//         public function setControllers()
//         {
//         	$front->setControllerDirectory(array(
//    			'default' => '../application/controllers',
//    			'blog'    => '../modules/blog/controllers',
//    			'news'    => '../modules/news/controllers',
//			));
//         }
//         public function setLogger()
//         {
//			$logger = new Zend_Log();
//			$writer = null;
//			if($this->_config->env == 'dev') {
//				$writer = new Zend_Log_Writer_Firebug();
//				$writer->setEnabled($this->_config->debug->on);
//			} else {
//				$writer = new Zend_Log_Writer_Null();
//			}
//			$logger->addWriter($writer);
//			Zend_Registry::set('logger',$logger);
//			require $this->_config->system . "Debug.php";
//         }
}

?>
