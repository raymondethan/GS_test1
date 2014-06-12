<?php

class StoryController extends App_Controller_Action
{

    protected $controller_name = 'story';
	
    public function indexAction()
    {
		$this->view->headMeta()->appendHttpEquiv('expires','-1')->appendHttpEquiv('pragma', 'no-cache')->appendHttpEquiv('Cache-Control', 'no-cache');
		$mode = Zend_Registry::get('mode');
		
        $story_id = (int)$this->_getParam('story_id');
		
		// TDM need to check to see if the user has promoted the story previously or not
					
					$this->view->is_promoted_by_user = false;
					$fb_user_id = $this->facebook->getUser();
					$this->view->facebook_user_id = $fb_user_id;
					
					if ($fb_user_id) {
								$user_model = $this->__getModel('User');
								$users = $user_model->getUsers(array('fields' => array('id'),'fb_user_id' => $fb_user_id));
							if (isset($users[0])) {
								$user = $users[0]->toArray();
								$log_model = $this->__getModel('Log');
								$is_promoted = $log_model->hasBeenPromotedByUser($user['id'], $story_id);
								$this->view->is_promoted_by_user = $is_promoted;
								$this->view->test = "THIS IS A TEST 1";	
							}
							else {
								$this->view->is_promoted_by_user = false;
								$this->view->test = "THIS IS A TEST 2";	
							}
					}
					else{
						$this->view->is_promoted_by_user = false;
						$this->view->test = "THIS IS A TEST 3";	
					} 
		

	
        if ($story_id > 0) {
            $story_model = $this->__getModel('Story');
            $stories = $story_model->find($story_id);

            if (isset($stories[0])) {
			   if ($mode == 'default') {
				
                	$category_model = $this->__getModel('Category');
                	$location_model = $this->__getModel('Location');
                	$media_model = $this->__getModel('Media');
                	$producer_model = $this->__getModel('Producer');
                	$ad_model = $this->__getModel('Ad');
                	
					$story = $stories[0]->toArray();
					$story['thumbnail'] = ($story['thumbnail'] != '' && file_exists(ROOT_PATH.'/public/uploads/story/'.$story['thumbnail'])) ? '/uploads/story/'.$story['thumbnail'] : '/images/logo.jpg';
					
					preg_match_all('#(www\.|https?:\/\/){1}[a-zA-Z0-9]{2,}\.[a-zA-Z0-9]{2,}(\S*)#i',$story['meta_description'],$matches,PREG_PATTERN_ORDER);
					$story['links'] = $matches[0];

					$tags = explode(";",trim($story['tags']));
					$categories = $category_model->find($story['category']);
					$locations = $location_model->find($story['location']);
					$medias = $media_model->find($story['media']);
					$producers = $producer_model->find($story['producer']);
					$ad = $ad_model->getRandomAd(array('state' => 1));
					
					$this->view->story = $story;
					$this->view->tags = $tags;
					$this->view->category = (isset($categories[0])) ? $categories[0] : array();
					$this->view->location = (isset($locations[0])) ? $locations[0] : array();
					$this->view->media = (isset($medias[0])) ? $medias[0] : array();
					$this->view->producer = (isset($producers[0])) ? $producers[0] : array();
					$this->view->ad = ($ad) ? $ad : array();
					

					if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
						$this->_helper->Layout->disableLayout();
					
						$db = Zend_Registry::get('db');
						$db->update('Story',array('popularity' => new Zend_Db_Expr('popularity+1')),'id = '.$story['id']);
						$db->update('Category',array('popularity' => new Zend_Db_Expr('popularity+1')),'id = '.$story['category']);
						$db->update('Tag',array('popularity' => new Zend_Db_Expr('popularity+1')),'name IN ("'.implode('","',$tags).'")');
						$db->update('Media',array('popularity' => new Zend_Db_Expr('popularity+1')),'id = '.$story['media']);
						if (!($is_promoted)){
							$this->_log(array('content_id' => $story['id'],'content_type' => 'story','action' => 'watched','info' => ''));
						}
						
					}
					else {
					
						$this->setPageTitle($story['name']);
						$this->setPageKeywords($story['keywords']);
						$this->setPageMeta($story['meta_description']);
						
						parse_str($this->_getParam('params',''),$params);
						$this->view->url_params = $params;
						
						$params['fields'] = array('id','name','url_title','location','thumbnail','category','media');
						$params['state'] = 1;
						$this->view->params = $params;

				    	$params_without_country = $params;
				    	unset($params_without_country['country']);
				    	$this->_getStories($params_without_country);
				        $this->setMainViewVars($params);
						$this->_getMapStories($this->view->data);
						
					}
				
	
                }
                else {
                    $this->view->data = $stories->toArray();
                }
            }
        }
    }

    public function saveAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
    	$request = $this->getRequest();
        
        if ($request->isPost() && !is_null($identity)) {
            $story_id = $request->getParam('story_id');
            $story_model = $this->__getModel('Story');
            $location_model = $this->__getModel('Location');
            $geolocation_model = $this->__getModel('GeoLocation');
			
            $duration_parts = explode(":",$request->getParam('duration',''));
            $duration = (int)$duration_parts[0] * 60 + (int)$duration_parts[1];
            
            $story_data = array('language' => $request->getParam('language',''),
            					'name' => $request->getParam('name',''),
                                'url_title' => $request->getParam('url_title',''),
                                'description' => $request->getParam('description',''),
            					'meta_description' => $request->getParam('meta_description',''),
                                'keywords' => $request->getParam('keywords',''),
                                'thumbnail' => ((int)$request->getParam('no_thumbnail') > 0) ? '' : $request->getParam('thumbnail',''),
                                'state' => (int)$request->getParam('state'),
                                //'organisation_type' => (int)$request->getParam('organisation_type'),
                                'producer' => ($identity->producer != 0) ? $identity->producer : (int)$request->getParam('producer'),
                                'media' => (int)$request->getParam('media'),
            					'media_source_url' => $request->getParam('media_source_url',''),
                                'media_content_url' => $request->getParam('media_content_url',''),
                                'category' => (int)$request->getParam('category'),
                                'tags' => ';'.implode(";",$request->getParam('tags',array())).';',
                                'hashtag' => $request->getParam('hashtag',''),
                                'location' => (int)$request->getParam('location'),
                                'adult_content' => (int)$request->getParam('adult_content'),
            					'date' => $request->getParam('date',''),
            					'duration' => $duration,
                                'date_updated' => date("Y-m-d H:i:s"),
            					'updated_by' => Zend_Auth::getInstance()->getIdentity()->id
                                );

            if ($story_id > 0) {
                $story_model->update($story_data,'id='.$story_id);
                $geolocation_model->delete("content_id = '".$story_id."' AND content_type = 'story'");
                $this->_log_admin(array('content_type' => 'story','action' => 'updated','info' => $story_data['name']));
            }
            else {
                $story_data['user'] = Zend_Auth::getInstance()->getIdentity()->id;
                $story_data['date_created'] = date("Y-m-d H:i:s");
                $story_id = $story_model->insert($story_data);
                $this->_log_admin(array('content_type' => 'story','action' => 'created','info' => $story_data['name']));
            }
			
            $geolocations = $request->getParam('geolocation',array());
            foreach ($geolocations as $k => $latlng) {
	            $geolocation_data = array('content_id' => $story_id,'content_type' => 'story','latlng' => $latlng);
	            ($k == 0) ? $geolocation_data['primary'] = 1 : '';
	            $geolocation_model->insert($geolocation_data);
            }

            // Stories_index
            try {
                $index = Zend_Search_Lucene::open('search/stories_index');
            }
            catch (Zend_Exception $e) {
                $index = Zend_Search_Lucene::create('search/stories_index');
            }
            
            $doc_ids = $index->termDocs(new Zend_Search_Lucene_Index_Term($story_id,'db_id'));
            (isset($doc_ids[0])) ? $index->delete($doc_ids[0]) : '';

            $story_data['id'] = $story_id;
            	$locations = $location_model->getLocations(array('id' => $story_data['location'],'city_name' => 1,'country_name' => 1,'continent_name' => 1));
			$location = (isset($locations[0])) ? $locations[0] : array();
			
            $this->_addStoryToSearchIndex($index,$story_data,$location);
            
            $redirect_url = $request->getParam('redirect_url',NULL);
            if (!is_null($redirect_url)) {
            		$this->_helper->flashMessenger->addMessage(array('type' => 'success','text' => $this->view->i18n->t('content.type_story').' "'.$story_data['name'].'" <b>'.$this->view->i18n->t('success.successfully_saved').'</b>'));
                $this->_helper->redirector->gotoUrl($redirect_url);
            }
        }
    }

}

?>
