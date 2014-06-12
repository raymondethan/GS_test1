<?php

class HomeController extends App_Controller_Action
{
	
    protected $controller_name = 'home';

    public function indexAction()
    {	
    	$this->setPageTitle('');
    	parse_str($this->_getParam('params',''),$params);

    	$comma_separated_params = array('cio','tags','producers');
    	foreach ($comma_separated_params as $v) {
    		if (isset($params[$v])) {
    			$parts = explode(",",$params[$v]);
    			sort($parts);
    			$params[$v] = implode(",",$parts);
    		}
    	}
    	
    	$producer_id = (int)$this->_getParam('producer_id');
    	if ($producer_id > 0) {
    		$params['producers'] = $producer_id;
    	}
    	
    	$this->view->url_params = $params;
    	
    	$params['fields'] = array('id','name','url_title','location','thumbnail','category','media');
    	$params['state'] = 1;
    	$this->view->params = $params;
    	
    	$params_without_country = $params;
    	unset($params_without_country['country']);
    	
    	$in = $this->_getStories($params_without_country);
    	(!is_null($in)) ? $params = array_merge($params,array('in' => $in)) : '';
    	
        $this->setMainViewVars($params);
		$this->_getMapStories($this->view->data);
		
		$this->view->active_footer_menu = $this->_getParam('active_footer_menu');
    }

    public function menuAction()
    {
    	$this->_disableNotPartialUrl();
    	$type = $this->_getParam('type','main');
        
        $menu_model = $this->__getModel('Menu');
        $menus = $menu_model->getMenus(array('type' => $type,'active' => 1))->toArray();

        foreach ($menus as $k => $menu) {
            $menu_params = array();
            $menu_params_parts = explode(";",$menu['params']);

            foreach ($menu_params_parts as $part) {
                $pv_ar = explode("=",$part);
                (count($pv_ar) == 2) ? $menu_params[trim($pv_ar[0])] = trim($pv_ar[1]) : '';
            }

            $menus[$k]['params'] = $menu_params;
        }

        $this->view->type = $type;
        $this->view->menus = $menus;
        $this->view->set_active = $this->_getParam('set_active','');
    }

    public function selectedAction()
    {
        $request = $this->getRequest();
        $content_type = strtolower($request->getParam('content_type',''));
        $allowed_content_types = array('story','tag','category','media','organisation');

        if ($request->isPost() && in_array($content_type, $allowed_content_types)) {
            $content_id = (int)$request->getParam('id');

            if ($content_id > 0) {
                $db = Zend_Registry::get('db');
                $db->query("UPDATE `".ucfirst($content_type)."` SET popularity = popularity + 1 WHERE id = ".$content_id);
            }
        }
    }
	
	
	public function sendFacebookMapUpdatesAction(){
			
			$log_model = $this->__getModel('Log');
			$users = $log_model->getFacebookMapUpdates();
		
			for ($i=0;$i<count($users);$i++){
				$user = $users[$i];
				$user_name = $user['first_name']." ".$user['last_name'];
				$message = $user_name." has updated their map. Share the Global Stories you care about by creating yours.";
				echo $message."<br />";
				$attachment = array(
					'message' => $message,
					'name' => 'Global Stories',
					'link' => 'http://www.globalstories.org',
					'description' => 'Learn about the world we live in',
					'picture'=>'http://www.globalstories.org/images/GS_log_thumb.jpg',
					'privacy'=> array('value'=>'EVERYONE')
					
				);
				try{
					$call = '/'.$user['fb_user_id'].'/feed';
					$this->facebook->api($call , 'POST', $attachment);

				} catch (FacebookApiException $e) {
						echo($e);
						echo "<br />";
						$facebook_user = null;
				}
		}
			die();
			
	}

    /*public function suggestAction()
    {
        $request = $this->getRequest();
        $content_type = strtolower($request->getParam('content_type',''));
        $allowed_content_types = array('story');

        if ($request->isPost() && in_array($content_type, $allowed_content_types)) {
            $suggest_data = array('type' => $content_type);
            $valid_data = true;

            (trim($request->getParam('title','')) != '') ? $suggest_data['title'] = trim($request->getParam('title','')) : $valid_data = false;
            (trim($request->getParam('url','')) != '') ? $suggest_data['url'] = trim($request->getParam('url','')) : $valid_data = false;
            (trim($request->getParam('reason','')) != '') ? $suggest_data['reason'] = trim($request->getParam('reason','')) : $valid_data = false;

            if ($valid_data !== false) {
                $suggest_model = $this->__getModel('Suggest');
                $suggest_model->insert($suggest_data);
            }
        }
    }*/

/*    public function getStoriesAction()
    {
    	$this->_disableNotPartialUrl();
    	$category_model = $this->__getModel('Category');
    	$story_model = $this->__getModel('Story');
		$media_model = $this->__getModel('Media');
		$updates = $this->_getParam('updates',array());
    	$params = $this->_getParam('params',array());
    	$params['fields'] = array('id','name','url_title','location','thumbnail','category','media');
        $params['state'] = 1;
	
        $categories = $category_model->getCategories(array('sort_by' => 'order','order' => 'ASC'));
		$this->view->categories = array();
		
		foreach ($categories as $category) {
			$this->view->categories[$category['id']] = array('name' => $category['name'],'short_name' => $category['short_name']);
		}
		
    	$medias = $media_model->getMedias();
		$this->view->medias = array();
		
		foreach ($medias as $media) {
			$this->view->medias[$media['id']] = array('name' => $media['name'],'short_name' => $media['short_name']);
		}
		
		$data = array();
		$counter = array();
		
		if (in_array('stories',$updates)) {
			$params_without_country = $params;
			unset($params_without_country['country']);
			
	    	$in = $this->_getStories($params_without_country);
			$this->_getMapStories($this->view->data);
			
			if (isset($params['country']) || isset($params['tags']) || isset($params['user']) || isset($params['producers'])) {
				$params_without_country = $params; unset($params_without_country['country']);
				$params_without_country_tags = $params_without_country; unset($params_without_country_tags['tags']);
				$params_without_country_producers = $params_without_country; unset($params_without_country_producers['producers']);
				
				if (isset($params['tags']) && isset($params['producers'])) {
					$stories = $story_model->getStories(array_merge($params_without_country_tags,array('fields' => array('count' => 'COUNT(*)'))));
					$counter['tags_stories'] = $stories[0]['count'];
					$stories = $story_model->getStories(array_merge($params_without_country_producers,array('fields' => array('count' => 'COUNT(*)'))));
					$counter['producers_stories'] = $stories[0]['count'];
				}
				else {
					$stories = $story_model->getStories(array_merge($params_without_country,array('fields' => array('count' => 'COUNT(*)'))));
					$counter_stories_without_country = $stories[0]['count'];
					
					if (isset($params['tags'])) {
						$stories = $story_model->getStories(array_merge($params_without_country_tags,array('fields' => array('count' => 'COUNT(*)'))));
						$counter['tags_stories'] = $stories[0]['count'];
					}
					else {
						$counter['tags_stories'] = $counter_stories_without_country;
					}
					
					if (isset($params['producers'])) {
						$stories = $story_model->getStories(array_merge($params_without_country_producers,array('fields' => array('count' => 'COUNT(*)'))));
						$counter['producers_stories'] = $stories[0]['count'];
					}
					else {
						$counter['producers_stories'] = $counter_stories_without_country;
					}
				}
			}
			else {
				$counter['tags_stories'] = count($this->view->data);
				$counter['producers_stories'] = count($this->view->data);
			}
			
			$data['stories'] = $this->view->stories;
			$data['story_id_to_key'] = $this->view->story_id_to_key;
			$data['tags_producers_stories_count'] = $this->view->tags_producers_stories_count;
		}
		
		if (in_array('top_recent_stories',$updates)) {
			$top_recent_stories_params = array_merge($params,array('sort_by' => 'popularity','order' => 'DESC','limit' => $this->view->i18n->t('application.top_recent_stories_limit')));
			@(!is_null($in)) ? $top_recent_stories_params = array_merge($top_recent_stories_params,array('in' => $in)) : '';
			
			$this->view->top_stories = $story_model->getStories(array_merge($top_recent_stories_params,array('sort_by' => 'popularity')));
			$this->view->recent_stories = $story_model->getStories(array_merge($top_recent_stories_params,array('sort_by' => 'date_created')));
			
			$this->view->items = $this->view->top_stories;
			$data['top_stories'] = $this->view->render('home/generate-top-recent-stories.phtml');
			$this->view->items = $this->view->recent_stories;
			$data['recent_stories'] = $this->view->render('home/generate-top-recent-stories.phtml');
		}
		
		if (isset($params['country']) || !in_array('stories',$updates)) {
			$stories = $story_model->getStories(array_merge($params,array('fields' => array('count' => 'COUNT(*)'))));
			$counter['now_showing_stories'] = $stories[0]['count'];
			$counter['type'] = (isset($params['country'])) ? 'country' : 'world';
		}
		else {
			$counter['now_showing_stories'] = count($this->view->data);
			$counter['type'] = 'world';
		}

		$data['counter'] = $counter;

		
		echo Zend_Json::encode($data);
    	die();
    }*/
    
    public function generateTagsListAction()
    {
    	$this->_disableNotPartialUrl();
    	$params = $this->_getAllParams();
    	$tag_model = $this->__getModel('Tag');
    	$params_without_country = $params;
    	unset($params_without_country['country']);
    	$this->view->tags = $tag_model->getTags(array('stories_count' => 1,'stories_filter' => array_merge($params_without_country,array('state' => 1))));
    	$this->view->params = $params;
    }
    
    public function generateProducersListAction()
    {
    	$this->_disableNotPartialUrl();
    	$params = $this->_getAllParams();
	    $producer_model = $this->__getModel('Producer');
	    $params_without_country = $params;
	    unset($params_without_country['country']);
	    $this->view->producers = $producer_model->getProducers(array('stories_count' => 1,'stories_filter' => array_merge($params_without_country,array('state' => 1))));
	    $this->view->params = $params;
    }
    
    public function logSearchTermAction()
    {
    	$this->_disableNotPartialUrl();
    	$term = strtolower(trim($this->_getParam('term','')));
    	
    	if ($term != '') {
    		$log_search_model = $this->__getModel('LogSearch');
    		$log_searchs = $log_search_model->getLogSearchs(array('term' => $term));
    		
    		if (isset($log_searchs[0])) {
    			$log_search_model->update(array('popularity' => new Zend_Db_Expr('popularity+1')),'id = '.$log_searchs[0]['id']);
    		}
    		else {
    			$log_search_model->insert(array('term' => $term,'popularity' => 1));
    		}
    	}
    	
    	die();
    }
    
    public function aboutGlobalStoriesAction()
    {
    	if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    		$this->_forward('index','home','default',array('active_footer_menu' => 'about-global-stories'));
    	}
    }
    
    public function contactAction()
    {
    	if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    		$this->_forward('index','home','default',array('active_footer_menu' => 'contact'));
    	}
    }
    
    public function contentNotOwnedByGlobalStoriesAction()
    {
    	if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    		$this->_forward('index','home','default',array('active_footer_menu' => 'content-not-owned-by-global-stories'));
    	}
    }
    
    public function termsOfUsageAction()
    {
    	if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    		$this->_forward('index','home','default',array('active_footer_menu' => 'terms-of-usage'));
    	}
    }

    public function privacyPolicyAction()
    {
    	if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    		$this->_forward('index','home','default',array('active_footer_menu' => 'privacy-policy'));
    	}
    }
    
/*   public function noFlashAction()
    {
    	$this->setPageTitle('');
    	$this->_helper->layout()->setLayout('no-flash');
    	$this->_helper->ViewRenderer->setNoRender();
    }*/

}

?>
