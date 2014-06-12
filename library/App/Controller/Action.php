<?php
 class App_Controller_Action extends Zend_Controller_Action
 {
    protected $__models;
    protected $controller_name = null;
    protected $action_name = null;
    protected $facebook = null;
	protected $mode = null;
	protected $acl = null;
	
    public function preDispatch()
    {
    	$this->setI18n();
    	$this->setVars();
        $this->setPageTitle();
		$this->setPageKeywords();
		$this->setPageMeta();
        
        if ($this->controller_name != 'admin') {
        	$this->setFacebook();
	        $this->setFrontCss();
	        $this->setFrontJs();
        }
        
        if ($this->controller_name == 'admin') {
	        $this->checkAdminAuth();
	        $this->setAdminCss();
	        $this->setAdminJs();
        }
    }

    public function postDispatch()
    {
        if ($this->mode == 'api') {
            $this->view->action(strtolower($_SERVER['REQUEST_METHOD']),'rest','default',array('content_type' => $this->getRequest()->getControllerName()));  
        }
        
        $messages = $this->_helper->_flashMessenger->getMessages();
        $msgs = array();

    	foreach ($messages as $message) {
            $msgs[$message['type']][] = $message['text'];
        }

	    $this->view->messages = $msgs;
	    $messages_html = $this->view->render('messages.phtml');
        $this->view->placeholder('messages')->set($messages_html);
    }
    
    protected function setVars()
    {
        $request = $this->getRequest();
        $this->controller_name = $request->getControllerName();
        $this->action_name = $request->getActionName();
        $this->view->controller_name = $this->controller_name;
        $this->view->action_name = $this->action_name;
        $this->mode = Zend_Registry::get('mode');
    }

    protected function setFrontCss()
    {
        $this->view->headLink()->appendStylesheet('/css/main.css');
        $this->view->headLink()->appendStylesheet('/css/front.css');
    }

    protected function setFrontJs()
    {
		$this->view->headScript()->appendFile('http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js');
		$this->view->headScript()->appendFile('/js/jquery-ui-1.8.16.custom.min.js');
		$this->view->headScript()->appendFile('/js/jsized.form.globalstories.jquery.min.js');
		$this->view->headScript()->appendFile('/js/jquery.mousewheel.js');
		$this->view->headScript()->appendFile('/js/jquery.jscrollpane.min.js');
		$this->view->headScript()->appendFile('/js/jquery.colorbox.min.js');
		$this->view->headScript()->appendFile('/js/jquery.collect.js');
		$this->view->headScript()->appendFile('/js/jquery.resize.min.js');
		$this->view->headScript()->appendFile('http://www.youtube.com/player_api');
		$this->view->headScript()->appendFile('http://widgets.twimg.com/j/2/widget.js');
		$this->view->headScript()->appendFile('/js/swfobject.js');
		$this->view->headScript()->appendFile('/js/jquery.json-2.3.min.js');
		$this->view->headScript()->appendFile('http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4f200ba95113e057');
		$this->view->headScript()->appendFile('/js/jquery.history.js');
		$this->view->headScript()->appendFile('/js/highcharts.js');
    } 
    
    protected function setAdminCss()
    {
        $this->view->headLink()->appendStylesheet('/css/bootstrap.css');
    	$this->view->headLink()->appendStylesheet('/css/admin.css');
    }

    protected function setAdminJs()
    {
        $this->view->headScript()->appendFile('/js/jquery-1.5.2.min.js');
        $this->view->headScript()->appendFile('/js/jquery.maskedinput-1.3.min.js');
        $this->view->headScript()->appendFile('/js/admin.js');
    }

    protected function setI18n()
    {
        $this->view->i18n = $this->__getModel('I18n');
    }

    protected function setUploadify()
    {
        $this->view->headLink()->appendStylesheet('/uploadify/uploadify.css');
        $this->view->headScript()->appendFile('/js/swfobject.js');
        $this->view->headScript()->appendFile('/uploadify/jquery.uploadify.v2.1.4.min.js');
    }
    
    protected function setFancybox()
    {
        $this->view->headLink()->appendStylesheet('/css/jquery.fancybox-1.3.4.css');
        $this->view->headScript()->appendFile('/js/jquery.mousewheel-3.0.4.pack.js');
        $this->view->headScript()->appendFile('/js/jquery.fancybox-1.3.4.pack.js');       
    }
    
    protected function setGoogleMaps()
    {
        $this->view->headScript()->appendFile('http://maps.google.com/maps/api/js?sensor=false');
    	$this->view->headScript()->appendFile('/js/gmaps.js');
    }

    protected function setPageTitle($title = NULL) {
        $this->view->page_title = (!is_null($title)) ? $title : $this->view->i18n->t('title.'.$this->controller_name.'_'.$this->action_name);
    }
    
    protected function setPageKeywords($keywords = NULL) {
    	$this->view->page_keywords = (!is_null($keywords)) ? $keywords : $this->view->i18n->t('keywords.'.$this->controller_name.'_'.$this->action_name);
    }
    
    protected function setPageMeta($meta = NULL) {
    	$this->view->page_meta = (!is_null($meta)) ? $meta : $this->view->i18n->t('meta.'.$this->controller_name.'_'.$this->action_name);
    }
    
    protected function setMainViewVars($params = array())
    {
    	$cookie_name = str_replace(' ','_',strtolower($this->view->i18n->t('application.name'))).'_not_first_time_user';
    	
    	if (isset($_COOKIE[$cookie_name])) {
    		$this->view->first_time_user = false;
    	}
    	else {
    		$this->view->first_time_user = true;
    		setcookie($cookie_name,true,time()+10*365*24*3600);
    	}

    	$story_model = $this->__getModel('Story');
    	$category_model = $this->__getModel('Category');
		$organisation_model = $this->__getModel('Organisation');
		$media_model = $this->__getModel('Media');
		
		$categories = $category_model->getCategories(array('sort_by' => 'order','order' => 'ASC'));
		$this->view->categories = array();
		
		foreach ($categories as $category) {
			$this->view->categories[$category['id']] = array('name' => $category['name'],'short_name' => $category['short_name']);
		}
		
		$this->view->top_stories = $story_model->getStories(array_merge($params,array('sort_by' => 'popularity','order' => 'DESC','limit' => $this->view->i18n->t('application.top_recent_stories_limit'))));
		$this->view->recent_stories = $story_model->getStories(array_merge($params,array('sort_by' => 'date_created','order' => 'DESC','limit' => $this->view->i18n->t('application.top_recent_stories_limit'))));

		$counter = array();
		
		$stories = $story_model->getStories(array('fields' => array('count' => 'COUNT(*)'),'state' => 1));
		$counter['total_stories'] = $stories[0]['count'];
		
		if (isset($params['country'])) {
			$stories = $story_model->getStories(array_merge($params,array('fields' => array('count' => 'COUNT(*)'))));
			$counter['now_showing_stories'] = $stories[0]['count'];
			$counter['type'] = 'country';
		}
		else {
			$counter['now_showing_stories'] = count($this->view->data);
			$counter['type'] = 'world';
		}
		
		if (isset($params['country']) || isset($params['tags']) || isset($params['producers'])) {
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
		
		$this->view->counter = $counter;
		
		$this->view->organisation_types = $organisation_model->getOrganisationTypes(array('for_producers' => 1));
		
		$medias = $media_model->getMedias();
		$this->view->medias = array();
		
		foreach ($medias as $media) {
			$this->view->medias[$media['id']] = array('name' => $media['name'],'short_name' => $media['short_name']);
		}
		
		if (isset($params['country'])) {
			$stats_model = $this->__getModel('Stats');
			$this->view->stats = $stats_model->getStats(array('parent' => 0));
		}
    }
    
    protected function setFacebook()
    {
    	require_once ROOT_PATH.'/library/App/Facebook/facebook.php';
    	$this->facebook = new Facebook(array('appId' => $this->view->i18n->t('application.facebook_id'),'secret' => $this->view->i18n->t('application.facebook_secret')));
    }
    
    /*protected function checkFbAuth()
    {
    	$request = $this->getRequest();
    	$user = array();
    	$signed_request = $this->_parse_signed_request($request->getCookie('fbsr_'.$this->view->i18n->t('application.facebook_id'),NULL),$this->view->i18n->t('application.facebook_secret'));

    	if (!is_null($signed_request)) {
    		$db = Zend_Registry::get('db');
    		$user_model = $this->__getModel('User');
    		$users = $user_model->getUsers(array('fb_user_id' => $signed_request['user_id']));

    		if (isset($users[0]) && (strtotime($users[0]->date_updated) + 24 * 3600) > time()) {
    			$user = $users[0]->toArray();
    		}
    		else {
    			$access_token_response = file_get_contents('https://graph.facebook.com/oauth/access_token?client_id='.$this->view->i18n->t('application.facebook_id').'&redirect_uri=&client_secret='.$this->view->i18n->t('application.facebook_secret').'&code='.$signed_request['code']);
    			parse_str($access_token_response);
    				
    			@$fb_user = Zend_Json::decode(file_get_contents('https://graph.facebook.com/me?access_token='.$access_token));
	    		if ($fb_user['verified']) {
		  			$user = array('fb_user_id' => $fb_user['id'],
		  						  'first_name' => $fb_user['first_name'],
		  						  'last_name' => $fb_user['last_name'],
		  						  'gender' => $fb_user['gender'],
		  					  	  'locale' => $fb_user['locale'],
		  					  	  'username' => $fb_user['username'],
		  					  	  'timezone' => (!is_null($fb_user['timezone'])) ? $fb_user['timezone'] : '',
		  					  	  'about' => (!is_null($fb_user['bio'])) ? $fb_user['bio'] : '',
		  					  	  'birthday' => date("Y-m-d",strtotime($fb_user['birthday'])),
		  					  	  'education' => serialize($fb_user['education']),
		  					  	  'hometown' => (!is_null($fb_user['hometown']['name'])) ? $fb_user['hometown']['name'] : '',
			  					  'political' => (!is_null($fb_user['political'])) ? $fb_user['political'] : '',
			  					  'religion' => (!is_null($fb_user['religion'])) ? $fb_user['religion'] : '',
			  					  'relationship_status' => $fb_user['relationship_status'],
			  					  'work' => serialize($fb_user['work']),
			  					  'date_updated' => date("Y-m-d H:i:s",time())
			  					 );

					if (isset($users[0])) {
						$user_model->update($user,'id = '.$users[0]->id);
						$user['id'] = $users[0]->id;
						$user['date_created'] = $users[0]->date_created;
					}
					else {
						$user['date_created'] = $user['date_updated'];
						$user['id'] = $user_model->insert($user);
					}

					$db->delete('UserFriends','user_id = '.$user['id']);
					$db->delete('UserInterests','user_id = '.$user['id']);
					$db->delete('UserLikes','user_id = '.$user['id']);
					$db->delete('UserMovies','user_id = '.$user['id']);
						
					$fb_user_friends = Zend_Json::decode(file_get_contents('https://graph.facebook.com/me/friends?access_token='.$access_token));
					$fb_user_interests = Zend_Json::decode(file_get_contents('https://graph.facebook.com/me/interests?access_token='.$access_token));
					$fb_user_likes = Zend_Json::decode(file_get_contents('https://graph.facebook.com/me/likes?access_token='.$access_token));
					$fb_user_movies = Zend_Json::decode(file_get_contents('https://graph.facebook.com/me/movies?access_token='.$access_token));
	
					foreach ($fb_user_friends['data'] as $friend) {
						$user_friend = $db->query('SELECT id FROM `User` WHERE `fb_user_id` = '.$friend['id'])->fetchObject();
						
						if ($user_friend) {
							$db->insert('UserFriends',array('user_id' => $user['id'],'user_friend_id' => $user_friend->id));
						}
					}
					foreach ($fb_user_interests['data'] as $interest) {
						$db->insert('UserInterests',array('user_id' => $user['id'],'fb_interest_id' => $interest['id'],'name' => $interest['name'],'category' => $interest['category']));
					}
		  			foreach ($fb_user_likes['data'] as $like) {
						$db->insert('UserLikes',array('user_id' => $user['id'],'fb_like_id' => $like['id'],'name' => $like['name'],'category' => $like['category']));
					}
		  			foreach ($fb_user_movies['data'] as $movie) {
						$db->insert('UserMovies',array('user_id' => $user['id'],'fb_movie_id' => $movie['id'],'name' => $movie['name'],'category' => $movie['category']));
					}
				}
    		}
    			
    		$user_shared_stories = $db->query('SELECT COUNT(*) as count FROM `Log` WHERE `user` = '.$user['id'].' AND `content_type` = "story" AND `action` = "shared"')->fetchObject();
    		$user['shared_stories_count'] = $user_shared_stories->count;
		}

  		Zend_Registry::set('user',$user);
    }*/
    
    protected function checkAdminAuth()
    {
    	if ($this->action_name != 'index') {
    		$identity = Zend_Auth::getInstance()->getIdentity();
    		
			if (is_null($identity)) {
				$this->_helper->flashMessenger->addMessage(array('type' => 'error','text' => $this->view->i18n->t('error.no_admin_permission')));
	    		return $this->_helper->redirector->gotoRoute(array(), 'admin', true, false);
			}
			else {
				$this->setAdminAcl();
					
				$params = array($this->action_name);
				($this->_getParam('content_type','') != '') ? $params[] = $this->_getParam('content_type',NULL) : '';
				($this->_getParam('content_action','') != '') ? $params[] = $this->_getParam('content_action',NULL) : '';
	
				if (!$this->acl->isAllowed($identity->user_type,NULL,implode("_",$params))) {
					$this->_helper->flashMessenger->addMessage(array('type' => 'error','text' => $this->view->i18n->t('error.no_admin_permission')));
	    			return $this->_helper->redirector->gotoRoute(array(), 'admin', true, false);
				}
			}
    	}
    }
    
    protected function setAdminAcl()
    {
    		$user_type = Zend_Auth::getInstance()->getIdentity()->user_type;
    		$user_type_acl_model = $this->__getModel('UserTypeAcl');
    		$user_type_acls = $user_type_acl_model->getUserTypeAcls($user_type);
    		$acl = new Zend_Acl();
    		$acl->addRole($user_type);
    		
    		if (count($user_type_acls) > 0) {
    			$acl->deny($user_type);
    			foreach ($user_type_acls as $user_type_acl) {
    				if ($user_type_acl['permission'] == 'allow') {
    					$acl->allow($user_type,NULL,$user_type_acl['params']);
    				}
    				else {
    					$acl->deny($user_type,NULL,$user_type_acl['params']);
    				}
    			}
    		}
    		else {
    			$acl->allow($user_type);
    		}
    		
    		$this->acl = $acl;
    		$this->view->acl = $this->acl;
    }
    
 	protected function _parse_signed_request($signed_request,$secret) 
 	{
  		@list($encoded_sig,$payload) = explode(".",$signed_request,2); 

	    // decode the data
	    $sig = $this->_base64_url_decode($encoded_sig);
	    $data = Zend_Json::decode($this->_base64_url_decode($payload));

	    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
	    		error_log('Unknown algorithm. Expected HMAC-SHA256');
	    		return null;
	  	}

  		// check sig
  		$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
  		if ($sig !== $expected_sig) {
    			error_log('Bad Signed JSON signature!');
    			return null;
  		}

  		return $data;
	}

	protected function _base64_url_decode($input) {
  		return base64_decode(strtr($input, '-_', '+/'));
	}
	
	protected function _log($params = array())
	{
		$fb_user_id = $this->facebook->getUser();

		if ($fb_user_id) {
			$user_model = $this->__getModel('User');
			$users = $user_model->getUsers(array('fields' => array('id'),'fb_user_id' => $fb_user_id));
			
			if (isset($users[0])) {
				$user = $users[0]->toArray();
				$log_data = array('user' => $user['id'],'date' => date("Y-m-d H:i:s",time()));
				

				$log_data = array_merge($params,$log_data);
				$db = Zend_Registry::get('db');
				$log = $db->query('SELECT id FROM `Log` WHERE `user` = '.$log_data['user'].' AND `content_id` = '.$log_data['content_id'].' AND `content_type` = "'.$log_data['content_type'].'"')->fetchObject();
				
				if ($log) {
					$is_new = false;
					$db->update('Log',$log_data,'id = '.$log->id);
				}
				else {
					$is_new = true;
					$db->insert('Log',$log_data);
				}
				
				return $is_new;
			}
		}
	}
	
    protected function _log_admin($params = array())
    {
    	$log_data = array('user' => Zend_Auth::getInstance()->getIdentity()->id,'date' => date("Y-m-d H:i:s",time()));
    	
    	if ($log_data['user'] > 0) {
    		$log_data = array_merge($params,$log_data);
	    	$db = Zend_Registry::get('db');
		    $db->insert('LogAdmin',$log_data);
    	}
    }
    
    protected function _getContentTypeTableName($content_type)
    {
	    	$ar = array('story' => 'Story',
	    				'organisation-type' => 'OrganisationType',
	    				'producer' => 'Producer',
	    				'media-type' => 'Media',
	    				'category' => 'Category',
	    				'location' => 'Location',
	    				'country' => 'Country',
	    				'city' => 'City',
	    				'continent' => 'Continent',
	    				'tag' => 'Tag',
	    				'organisation' => 'Organisation',
	    				'user' => 'UserAdmin',
	    				'ad' => 'Ad');
	    	
	    	return $ar[$content_type];
    }
    
    protected function _getContentTypeDefaultListParams($content_type)
    {
	    	$ar = array('story' => array('sort_by' => 'date_updated','order' => 'DESC'),
	    				'category' => array('sort_by' => 'name','order' => 'ASC'),
	    				'tag' => array('sort_by' => 'stories_count','order' => 'DESC'),
	    				'location' => array('sort_by' => 'name','order' => 'ASC'),
	    				'country' => array('sort_by' => 'name','order' => 'ASC'),
	    				'city' => array('sort_by' => 'name','order' => 'ASC'),
	    				'continent' => array('sort_by' => 'name','order' => 'ASC'),
	    				'organisation' => array('sort_by' => 'name','order' => 'ASC'),
	    				'organisation-type' => array('sort_by' => 'name','order' => 'ASC'),
	    				'producer' => array('sort_by' => 'name','order' => 'ASC'),
	    				'media-type' => array('sort_by' => 'name','order' => 'ASC'),
	    				'user' => array('sort_by' => 'name','order' => 'ASC'),
	    				'ad' => array('sort_by' => 'name','order' => 'ASC'));
	    	
	    	return (isset($ar[$content_type])) ? $ar[$content_type] : array();
    }
    
    protected function _secondsToDhms($seconds)
    {
        $result = array();
        $result['day'] = floor($seconds / (60 * 60 * 24));
        $remainder = $seconds % (60 * 60 * 24);
        $result['hour'] = floor($remainder / (60 * 60));
        $remainder = $remainder % (60 * 60);
        $result['minute'] = floor($remainder / 60);
        $result['second'] = $remainder % 60;
        $remainder = $seconds % (60 * 60 * 24);
        
        return $result;
    }
    
    protected function __getModel($name = null)
    {
		if(is_null($name)) {
            $name = $this->controller_name;
        }
        
        if(!isset($this->__models[$name]))
        {
            if (is_string($name)) {
            try {
               	@Zend_Loader::loadClass($name);
            } catch (Zend_Exception $e) {
               	require_once 'Zend/Controller/Action/Exception.php';
               	throw new Zend_Controller_Action_Exception($e->getMessage());
            }
            $this->__models[$name] = new $name();
            }
        }
        
 		return $this->__models[$name];
    }

    protected function getZendSession($name) {
    		if(!Zend_Session::isStarted()) {
        		Zend_Session::start();
      	}
      	
        return new Zend_Session_Namespace($name);
    }

    protected function checkDebugLevel()
    {
        $debug_level = $this->_getParam('debug', 'false');
        if( $debug_level )
        {
            Zend_Registry::set('debug', $debug_level);
        }
    }
    
    protected function _addStoryToSearchIndex($index,$story,$location)
    {
    	$doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Keyword('db_id',$story['id']));
        $doc->addField(Zend_Search_Lucene_Field::Text('name',$story['name']));
        $doc->addField(Zend_Search_Lucene_Field::Text('description',$story['description']));
        $doc->addField(Zend_Search_Lucene_Field::Text('meta_description',$story['meta_description']));
        $doc->addField(Zend_Search_Lucene_Field::Text('tags',$story['tags']));
        $doc->addField(Zend_Search_Lucene_Field::Text('location',$location['name']));
        $doc->addField(Zend_Search_Lucene_Field::Text('city',$location['city_name']));
        $doc->addField(Zend_Search_Lucene_Field::Text('country',$location['country_name']));
        $doc->addField(Zend_Search_Lucene_Field::Text('continent',$location['continent_name']));
            
        $story_keywords = explode(",",$story['keywords']);
        $i = 0;
        foreach ($story_keywords as $keyword) { $i++;
        	$doc->addField(Zend_Search_Lucene_Field::Keyword('keyword_'.$i,trim($keyword)));
      	}

        $index->addDocument($doc);
        $index->commit();
        $index->optimize();
    }
    
    protected function _getStories($params = array())
    {	
    	$story_model = $this->__getModel('Story');
	    $identity = Zend_Auth::getInstance()->getIdentity();
	    $params = array_merge($this->_getContentTypeDefaultListParams('story'),$params);
      	($this->controller_name == 'admin' && $identity->producer > 0) ? $params['producer'] = $identity->producer : '';        
        $this->view->data = array();
        
        if (isset($params['search'])) {
            $search_value = $params['search'];
            
            if (trim($search_value) != '')
                try {
                    $index = Zend_Search_Lucene::open('search/stories_index');
                }
                catch (Zend_Exception $e) {
                    $index = Zend_Search_Lucene::create('search/stories_index');
                }
                
                Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive());
                Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(0);
                
                $hits = $index->find($search_value.'*');
                $params['in'] = array();
                
                if (count($hits) > 0) {
                    if (!isset($params['sort_by']) && !isset($params['order'])) {
                        foreach ($hits as $hit) {
	                        $stories = $story_model->find($hit->db_id);
	                        if (isset($stories[0])) {
	                            $this->view->data[] = $stories[0]->toArray();
	                        }
                    	}
                	}
                	else {						
	                    foreach ($hits as $hit) {
	                        $params['in'][] = $hit->db_id;
	                    }

	                    $stories = $story_model->getStories($params);
	                    if (count($stories) > 0) {
	                        $this->view->data = $stories;
	                    }
                	}
            	}
            	
            	return	$params['in'];
        }
        elseif ($this->mode == 'default') {
            $stories = $story_model->getStories($params);
            $this->view->data = $stories;
        }
    }
    
    protected function _getMapStories($stories)
    {
        $location_model = $this->__getModel('Location');
    	$geolocation_model = $this->__getModel('GeoLocation');
        $this->view->stories = array();
        $this->view->story_id_to_key = array();
        $i = 0;
        		
        foreach ($stories as $story) 
        {
            $locations = $location_model->getLocations(array('fields' => array(),'id' => $story['location'],'continent_code' => 1,'country_iso2' => 1));
        	$geolocations = $geolocation_model->getGeoLocations(array('content_id' => $story['id'],'content_type' => 'story'));
        	
        	if (count($locations) > 0 && count($geolocations) > 0){
        		$story_locations = array();

	       	    foreach ($geolocations as $geolocation) {
	       			$latlng = preg_replace(array('/[()]/'),array(''),$geolocation['latlng']);
	       			$latlng_parts = explode(",",$latlng);
	       			
	       			$story_locations[] = array('primary' => (bool)$geolocation['primary'],
	       									   'lat' => trim($latlng_parts[0]),
	       									   'long' => trim($latlng_parts[1]),
	       									   'continent' => $locations[0]['continent_code'],
	       									   'country' => $locations[0]['country_iso2']
	       									  );
	       		}

	       		$story_flash = array('id' => $story['id'],
	       						     'title' => $story['name'],
	       						     'url' => 'http://'.$_SERVER['HTTP_HOST'].$this->view->url(array('story_id' => $story['id'],'url_title' => $story['url_title']),'story',true,false),
	       						     'thumb_url' => ($story['thumbnail'] != '') ? '/uploads/story/'.$story['thumbnail'] : '/images/no_thumbnail.gif',
	       						     'category' => strtolower($this->view->categories[$story['category']]['short_name']),
	       						     'type' => strtolower($this->view->medias[$story['media']]['name']),
	       							 'locations' => $story_locations
	       							);
	       		
	       		$this->view->stories[$i] = $story_flash;
	       		$this->view->story_id_to_key[$story_flash['id']] = $i;
	       		$i++;
        	}        		
        }
    }

    protected function _setPaginator($data,$per_page,$page)
    {
        $paginator = Zend_Paginator::factory($data);
        $paginator->setItemCountPerPage($per_page);
        $paginator->setCurrentPageNumber($page);
        
        return $paginator;
    }
    
    protected function _disableNotPartialUrl()
    {
    	$partial = $this->_getParam('partial',NULL);
    	if (!is_null($partial) && $partial === true) {
    		return;
    	}
    	
    	if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	    	header("HTTP/1.1 301 Moved Permanently");
	    	header("Location: http://".$_SERVER['HTTP_HOST']);
	    	die();
    	}
    }

}

?>
