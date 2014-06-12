<?php

class AjaxController extends App_Controller_Action
{
	
	public function init()
	{
		//$this->_disableNotPartialUrl();
	}
	
	public function getFacebookUserAction()
	{
		$fb_user_id = $this->facebook->getUser();
		$user = array('error' => 'No User Available!');

		if ($fb_user_id) {
			$db = Zend_Registry::get('db');
			$user_model = $this->__getModel('User');
			$users = $user_model->getUsers(array('fields' => array('id','first_name','last_name','date_created','date_updated'),'fb_user_id' => $fb_user_id));
			
			if (isset($users[0])) {
				$user = $users[0]->toArray();
				
				$user['update_needed'] = ((strtotime($users[0]['date_updated']) + 24 * 3600) > time()) ? false : true;
				$user['member_since'] = date("Y",strtotime($user['date_created']));
				
				$user_shared_stories = $db->query('SELECT COUNT(1) as count FROM `Log` WHERE `user` = '.$user['id'].' AND `content_type` = "story" AND `action` = "shared"')->fetchObject();
				$user['shared_stories_count'] = $user_shared_stories->count;
                                
                // TMB
                $user_viewed_stories = $db->query('SELECT COUNT(1) as count FROM `Log` WHERE `user` = '.$user['id'].' AND `content_type` = "story" AND `action` = "watched"')->fetchObject();
				$user['viewed_stories_count'] = $user_viewed_stories->count;
			}
			else {
				try {
					$users = $this->facebook->api(array('method' => 'fql.query','query' => 'SELECT first_name,last_name FROM user WHERE uid = '.$fb_user_id));
					
					if (isset($users[0])) {
						$user = $users[0];
						$user['fb_user_id'] = $fb_user_id;
						$user['date_created'] = date("Y-m-d H:i:s",time());
						$user['date_updated'] = '00-00-00 00:00:00';
						
						$user['id'] = $user_model->insert($user);
						
						$user['update_needed'] = true;
						$user['member_since'] = date("Y",strtotime($user['date_created']));
						$user['shared_stories_count'] = 0;
					}
				}
				catch (Exception $e) {}
			}
			
			$user['fb_user_id'] = $fb_user_id;
		}

		echo Zend_Json::encode($user);
		die();
	}
	
	public function updateFacebookUserAction()
	{	
		$db = Zend_Registry::get('db');
		$user_model = $this->__getModel('User');
		
		$fb_user_id = $this->facebook->getUser();
		$users = $user_model->getUsers(array('fields' => array('id'),'fb_user_id' => $fb_user_id));
		
		if (isset($users[0])) {
			try {
				$fb_user = $this->facebook->api('/me');
				$user = array('fb_user_id' => $fb_user['id'],
						'first_name' => $fb_user['first_name'],
						'last_name' => $fb_user['last_name'],
						'gender' => $fb_user['gender'],
						'locale' => $fb_user['locale'],
						'username' => $fb_user['username'],
						'timezone' => (isset($fb_user['timezone']) && !is_null($fb_user['timezone'])) ? $fb_user['timezone'] : '',
						'about' => (isset($fb_user['bio']) && !is_null($fb_user['bio'])) ? $fb_user['bio'] : '',
						'birthday' => (isset($fb_user['birthday']) && !is_null($fb_user['birthday'])) ? date("Y-m-d",strtotime($fb_user['birthday'])) : '00-00-00 00:00:00',
						'education' => serialize($fb_user['education']),
						'hometown' => (isset($fb_user['hometown']['name']) && !is_null($fb_user['hometown']['name'])) ? $fb_user['hometown']['name'] : '',
						'political' => (isset($fb_user['political']) && !is_null($fb_user['political'])) ? $fb_user['political'] : '',
						'religion' => (isset($fb_user['religion']) && !is_null($fb_user['religion'])) ? $fb_user['religion'] : '',
						'relationship_status' => (isset($fb_user['relationship_status']) && !is_null($fb_user['relationship_status'])) ? $fb_user['relationship_status'] : '',
						'work' => serialize($fb_user['work']),
						'date_updated' => date("Y-m-d H:i:s",time())
				);
				
				$user_model->update($user,'id = '.$users[0]->id);
				$user['id'] = $users[0]['id'];
				
				$db->delete('UserFriends','user_id = '.$user['id']);
				$db->delete('UserInterests','user_id = '.$user['id']);
				$db->delete('UserLikes','user_id = '.$user['id']);
				$db->delete('UserMovies','user_id = '.$user['id']);
				
				$fb_user_friends = $this->facebook->api('/me/friends');
				$fb_user_interests = $this->facebook->api('/me/interests');
				$fb_user_likes = $this->facebook->api('/me/likes');
				$fb_user_movies = $this->facebook->api('/me/movies');
				
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
			catch (Exception $e) {
			}
		}
		
		die();
	}
	

	public function generateCommunityFeedAction()
	{
		$fb_user_id = $this->facebook->getUser();

        if ($fb_user_id) {
			$user_model = $this->__getModel('User');
			$users = $user_model->getUsers(array('fields' => array('id'),'fb_user_id' => $fb_user_id));
            
            if (isset($users[0])) {
				$user = $users[0]->toArray();
				$log_model = $this->__getModel('Log');
				$this->view->community_feeds = $log_model->getCommunityFeeds($user['id'],$this->view->i18n->t('application.community_feed_limit'));
				$this->view->fb_user_id = $fb_user_id;
			}
			else {
				die();
			}
		}
		else {
			die();
		}
	}
	
	
	public function hasBeenPromotedByUserAction(){
		$this->view->is_promoted_by_user = false;
		$fb_user_id = $this->facebook->getUser();
		$this->view->facebook_user_id = $fb_user_id;
		$story_id = $_REQUEST['story'];

		if ($fb_user_id) {
			$user_model = $this->__getModel('User');
			$users = $user_model->getUsers(array('fields' => array('id'),'fb_user_id' => $fb_user_id));
			if (isset($users[0])) {
				$user = $users[0]->toArray();
				$log_model = $this->__getModel('Log');
				$is_promoted = $log_model->hasBeenPromotedByUser($user['id'], $story_id);
				echo  $is_promoted;	
			}
			else {
				echo "0";
			}
		}
		else{
			echo "0";
		} 
		
		die(); 
	}
	
	/* 
		TDM
		CaLLS THE getCommunityFeeds() in the log model and sends the community_feeds variable to the related view which in this case is located in the following
		/globalstories/application/modules/default/views/scripts/ajax/generate-friends-map.phtml
	*/
	public function generateFriendsMapAction()
	{
		$fb_user_id = $this->facebook->getUser();
         if ($fb_user_id) {
			$user_model = $this->__getModel('User');
			$users = $user_model->getUsers(array('fields' => array('id'),'fb_user_id' => $fb_user_id));
            if (isset($users[0])) {
			
				$user = $users[0]->toArray();

				$log_model = $this->__getModel('Log');
				$maps= $log_model->getFriendsMaps($user['id'],$this->view->i18n->t('application.community_feed_limit'));
				$this->view->community_maps = $maps;
				//$this->view->community_maps = $log_model->getFriendsMaps($user['id'],$this->view->i18n->t('application.community_feed_limit'));
				$this->view->fb_user_id = $fb_user_id;
				$this->view->user_recommends =  $_REQUEST['userrecommends'];
				$this->view->user_views =  $_REQUEST['userviews'];
				
			}
			else {
				die();
			}
		}
		else {
			die();
		} 
		//die();
	}
	// end TDM
	
	public function generateUrlAction()
    {
        echo App_Filter_Output::stringURLSafe($this->_getParam('title',''));
        die();
    }
    
    public function generateListAction()
    {
        $params = $this->_getAllParams();
        
        switch ($params['type']) {
            case 'select':
                    $model = $this->__getModel($params['model']);

                    $params['label_field'] = (!isset($params['label_field'])) ? 'name' : $params['label_field'];
                    $params['value_field'] = (!isset($params['value_field'])) ? 'id' : $params['value_field'];
                    
                    if (isset($params['method']) && method_exists($model,$params['method'])) {
                        $result = (isset($params['params'])) ? $model->$params['method']($params['params']) : $model->$params['method']();
                    }
                    else {
                        $result = $model->fetchAll();
                    }
                break;
            case 'tags': 
                $model = $this->__getModel('Tag');
                $result = $model->getTags();
                break;
        }
        
        $this->view->params = $params;
        $this->view->result = $result;
        
        $this->_helper->viewRenderer->setRender('generate-list-'.$params['type']);        
    }
    
    public function generateLocationStringAction()
    {
        $id = $this->_getParam('id');
        $str_ar = array();
        
        $location_model = $this->__getModel('Location');
        $locations = $location_model->find($id);
        
        if (isset($locations[0])) {
            $city_model = $this->__getModel('City');
            $country_model = $this->__getModel('Country');
            $continent_model = $this->__getModel('Continent');
            
            $cities = $city_model->getCities(array('id' => $locations[0]['city']));
            $countries = $country_model->getCountries(array('id' => $locations[0]['country']));
            $continents = $continent_model->getContinents(array('id' => $locations[0]['continent']));

            
            (isset($cities[0])) ? $str_ar[] = $cities[0]['name'] : '';
            (isset($countries[0])) ? $str_ar[] = $countries[0]['name'] : '';
            (isset($continents[0])) ? $str_ar[] = $continents[0]['name'] : '';
        }
        
        echo implode(", ",$str_ar);
        die();
    }
    
    public function generateChartDataAction()
    {
    	$indicator = $this->_getParam('indicator','');
    	$country = strtoupper($this->_getParam('country',''));
    	
    	if ($indicator != '' && $country != '') {
    		$country_model = $this->__getModel('Country');
    		$result = $country_model->getCountries(array('fields' => array('iso2')));
    		$countries = array();
    		$values = array();
    		
    		foreach ($result as $res) {
    			$countries[$res['iso2']] = '';
    		}

    		$world_bank_data = array();
    		$page = 1;
    		$completed = false;
    		
    		while(!$completed) {
    			try {
    				$data = @Zend_Json::decode(file_get_contents('http://api.worldbank.org/countries/indicators/'.$indicator.'?page='.$page.'&per_page=1000&MRV=1&format=json'));
    				(!is_null($data[1])) ? $world_bank_data = array_merge_recursive($world_bank_data,$data[1]) : '';
    				($data[0]['page'] != $data[0]['pages']) ? $page++ : $completed = true;
    			}
    			catch (Exception $e) {
    				$completed = true;
    			}
    		}
    		
    		foreach ($world_bank_data as $data) {
    			if (isset($countries[$data['country']['id']]) && !is_null($data['value']) && $data['value'] != 0) {
    				$countries[$data['country']['id']] = $data['country']['value'];
   					$values[$data['country']['id']] = (float)$data['value'];
    			}
    		}

    		$chart_data = array('error' => 'No Stats Available!');
    		
    		if (count($values) > 0) {
    			$max_value = max($values);
    			foreach ($values as $k => $v) {
    				if ($v <= ($max_value * 0.01)) {
    					unset($values[$k]);
    				}
    			}
    			if (count($values) > 0) {
    				$chart_data = array('countries' => array(),'values' => array());
    				$random_values_number = 8;
    				
    				if (isset($values[$country])) {
    					$chart_data['countries'][] = $countries[$country];
    					$chart_data['values'][] = $values[$country];
    					
    					unset($countries[$country]);
    					unset($values[$country]);
    				}
    				else {
    					$random_values_number++;
    				}
    				
    				if ( (count($values) > 0) && ($max_country = array_search($max_value,$values)) ) {
    					$chart_data['countries'][] = $countries[$max_country];
    					$chart_data['values'][] = $max_value;
    					
    					unset($countries[$max_country]);
    					unset($values[$max_country]);
    				}

    				if ( (count($values) > 0) && ($min_value = min($values)) && ($min_country = array_search($min_value,$values)) ) {
    					$chart_data['countries'][] = $countries[$min_country];
    					$chart_data['values'][] = $min_value;
    					
    					unset($countries[$min_country]);
    					unset($values[$min_country]);
    				}

    				asort($values);
    				$values_keys = array_keys($values);
    				$from = (int)((count($values_keys) - $random_values_number) / 2);
    				$to = $from + $random_values_number;
    				
    				for ($i = $from; $i < $to; $i++) {
    					if (isset($values_keys[$i])) {
	    					$chart_data['countries'][] = $countries[$values_keys[$i]];
	    					$chart_data['values'][] = $values[$values_keys[$i]];
    					}
    				}
    			}
    		}

    		echo Zend_Json::encode($chart_data);
    	}
    	
    	die();
    }
    
    public function logPromotedStoryAction()
    {
    	$request = $this->getRequest();
    	
    	if ($request->isPost()) {
    		$story_id = (int)$request->getParam('story_id');
    		$facebook_post_id = $request->getParam('facebook_post_id','');
    		if ($story_id > 0 && $facebook_post_id != '') {
    			$this->_log(array('content_id' => $story_id,'content_type' => 'story','action' => 'shared','info' => $facebook_post_id));
    		}
			
    	}
    	
    	die();
    }
    
    // TMB: TDM - This action should remove the entry from the DB
    // TODO: you also need to work out whether or not you want to remove just the logged View or the Promoted Stories (when they exist?).
    public function removeViewedStoryAction()
    {
    	$request = $this->getRequest();
		echo "Attempting to remove story \n ";
		
    	if ($request->isPost()) {
			
    		$story_id = (int)$request->getParam('story_id');
			
    		if ($story_id > 0) {
						echo "Attempting to remove story (step 2) \n ";
						$fb_user_id = $this->facebook->getUser();
			
						if ($fb_user_id) {
							echo "Attempting to remove story (step 3) \n  ";
							$user_model = $this->__getModel('User');
							$users = $user_model->getUsers(array('fields' => array('id'),'fb_user_id' => $fb_user_id));
						
							if (isset($users[0])) {
								
								$user = $users[0]->toArray();
								echo "Attempting to remove story (step 4)".$user['id']." \n ";
								$log_model = $this->__getModel('Log');
								$sql = $log_model->removeStoryFromMap($user['id'], $story_id);
								echo $sql."\n";
								echo "removing ".$story_id." from user id".$user['id'];
							}
					
							//else {
								//die();
							//}
						}
						else {
							//die();
						}
					
    		}
    	}
    	
    	die();
    }
    
    /*protected function _buildGoogleChartUrl($data,$x_data_key,$y_data_key)
    {
    	if (is_array($data))
    	{
    		$url = $this->view->i18n->t('google.chart.url');
    		$url_params = array();
    
    		$formatted_data = array();
    		foreach ($data as $k => $v) {
    			$formatted_data[$v[$y_data_key][0]] = $v[$x_data_key];
    		}
    
    		$max_value = max($formatted_data);
    		if ($max_value != 0) {
    			foreach ($formatted_data as $k => $v) {
    				$formatted_data[$k] = $v/$max_value*100;
    			}
    		}
    
    		$url_params = array('cht' => $this->view->i18n->t('google.chart.cht'),
    				'chs' => $this->view->i18n->t('google.chart.chs'),
    				'chxt' => 'x,y',
    				'chxr' => '1,0,'.$max_value,
    				'chd' => 't:'.implode(",",$formatted_data),
    				'chxl' => '0:|'.implode("|",array_keys($formatted_data))
    		);
    
    		return $url.'?'.http_build_query($url_params);
    	}
    	else {
    		return false;
    	}
    }*/
    
}

?>
