<?php

class AdminController extends App_Controller_Action
{
    protected $controller_name = 'admin';

    public function init()
    {
        ini_set('max_execution_time', 3600);
        ini_set('upload_max_filesize', '128M');
        ini_set('post_max_size', '128M');
        ini_set('memory_limit', '256M');
        $this->_helper->layout()->setLayout('admin');
    }

    public function indexAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $identity = Zend_Auth::getInstance()->getIdentity();
            
            if ($identity->producer > 0) {
                return $this->_helper->redirector->gotoRoute(array('action' => 'content'), 'admin', true, false);
            }
            else {
                return $this->_helper->redirector->gotoRoute(array('action' => 'dashboard'), 'admin', true, false);
            }
        }      
    }

    public function dashboardAction()
    {
        $this->setPageTitle($this->view->i18n->t('application.name').' '.$this->view->i18n->t('title.admin_index'));
    }

    public function contentAction()
    {
        $this->setFancybox();
        
        $blocks = array('story' => array('story','category','tag'),
                        'producer' => array('producer'),
                        'location' => array('location'),//array('location','country','city','continent')
                        'organisation' => array('organisation'),//array('organisation','organisation-type')
                        //'media' => array('media-type','media-display-format'),
                        'user' => array('user'),//array('user','user-interest','user-like','user-movie'),
                        'ad' => array('ad'),
                        //'landing-page' => array('landing-page')
                        );

        $content_type = $this->_getParam('content_type');
        
        if ($content_type) {
            parse_str($this->_getParam('params',''),$params);
            $content_action = $this->_getParam('content_action','list');
            
            foreach ($blocks as $block => $info) {
                if (array_search($content_type,$info) !== false) {
                    $content_block = $block;
                    break;
                }
            }
            
            $breadcrumbs = array($this->view->i18n->t('menu.content'),
                                 $this->view->i18n->t('content.block_title_'.$content_block),
                                 $this->view->i18n->t('content.type_'.$content_type)
                                );
            if ($content_action != 'list') {
                $breadcrumbs[] = ((int)@$params['id'] > 0) ? $this->view->i18n->t($content_type.'.edit') : $this->view->i18n->t($content_type.'.add');
            }

            $this->setPageTitle(implode(" > ",$breadcrumbs));
            $this->view->menu_set_active = 'content';
            
            $method_name = '_'.$content_action;
            if (method_exists($this, $method_name)) {
                $this->$method_name($content_type,$params);
            }
            
            $this->_helper->viewRenderer->setRender($content_type.'/'.$content_action);
        }
        else {
            $counter = array('story' => 0,'category' => 0,'tag' => 0,'location' => 0);
            $db = Zend_Registry::get('db');
            $identity = Zend_Auth::getInstance()->getIdentity();
                
            foreach ($counter as $k => $v) {
                $query = "SELECT COUNT(*) as count FROM `".$this->_getContentTypeTableName($k)."` WHERE 1";
                if ($k == 'story' && $identity->producer > 0) {
                    $query .= " AND producer = ".$identity->producer;
                }
                $rows = $db->query($query)->fetchAll();
                $counter[$k] = $rows[0]['count'];   
            }
            
            $log_admin_model = $this->__getModel('LogAdmin');
            
            $this->view->log = $log_admin_model->getAdminLogs($this->view->i18n->t('recent_changes.limit'));
            $this->view->counter = $counter;
            $this->view->blocks = $blocks;
        }
    }
    
    public function changePaginationItemsPerPageAction()
    {
        $user_admin_model = $this->__getModel('UserAdmin');
        $identity = Zend_Auth::getInstance()->getIdentity();
            $to = $this->_getParam('to',10);
        
            $user_admin_model->update(array('pagination_items_per_page' => $to),'id = '.$identity->id);
            $identity->pagination_items_per_page = $to;
            
            die();
    }
    
    public function usersAction()
    {
        $user_type_model = $this->__getModel('UserType');   
        $user_admin_model = $this->__getModel('UserAdmin');
            
        $user_types = $user_type_model->getUserTypes();
        $user_admins = $user_admin_model->getUserAdmins();
            
        $this->view->user_types = $user_types;
        $this->view->user_admins = array();
            
        foreach ($user_admins as $user_admin) {
            $this->view->user_admins[$user_admin['user_type']][] = $user_admin;
        }
    }
    
    public function importAction()
    {
        $file = $this->_getParam('file',NULL);

        if (!is_null($file) && file_exists(ROOT_PATH.'/public/uploads/import/'.$file)) {
            if(!function_exists('str_getcsv')) {
                function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = "\\") {
                    $fp = fopen("php://memory", 'r+');
                    fputs($fp, $input);
                    rewind($fp);
                    $data = fgetcsv($fp, null, $delimiter, $enclosure); // $escape only got added in 5.3.0
                    fclose($fp);
                    return $data;
                }
            }
            
            $file_ar = file(ROOT_PATH.'/public/uploads/import/'.$file);

            if (is_array($file_ar)) {
                $db = Zend_Registry::get('db');
                $db->query("DROP TABLE IF EXISTS `Statistics`;");
                
                $i = 0;
                foreach ($file_ar as $line) 
                {
                    $i++;
                    $line_ar = str_getcsv($line,",");
                    $line_ar = (is_array($line_ar[0])) ? $line_ar[0] : $line_ar;
                    
                    if ($i == 1) {
                        $cols = $line_ar;

                        $query = "CREATE TABLE IF NOT EXISTS `Statistics` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,";
                        foreach ($cols as $col) {
                            $query .= " `".$col."` varchar(255) NOT NULL,";
                        }
                        $query .= " PRIMARY KEY (`id`)
                                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;";

                        $db->query($query);
                    }
                    else {
                        $db->insert('Statistics',array_combine($cols,$line_ar));
                    }
                }
                die($this->view->i18n->t('success.import_statistics'));
            }
        }
        
        $fancybox = $this->_getParam('fancybox',NULL);
        if(!is_null($fancybox)) {
            $this->_helper->layout->setLayout('clear');
            $this->setUploadify();
        }
        else {
            die();
        }
    }

    public function assetsAction()
    {

    }

    public function reportsAction()
    {

    }
    
    public function areYouSureAction()
    {
        $this->view->content_type = $this->_getParam('content_type','');
        $this->view->content_action = $this->_getParam('content_action','');
    }
    
    public function systemToolsAction()
    {
        $task = $this->_getParam('task','');
        
        switch ($task) {
            case 'reindex-stories-search':
                $story_model = $this->__getModel('Story');
                $this->view->stories = $story_model->getStories(array('fields' => array('id'),'sort_by' => 'id','order' => 'ASC'));
                
                $stories_index_path = ROOT_PATH.'/public/search/stories_index';
                
                if (is_dir($stories_index_path)) {
                    if ($dh = opendir($stories_index_path)) {
                        while (($file = readdir($dh)) !== false) {
                            if (is_file($stories_index_path.'/'.$file)) {
                                unlink($stories_index_path.'/'.$file);
                            }
                        }
                        closedir($dh);
                    }
                }
        
                $index = Zend_Search_Lucene::create('search/stories_index');
                break;
            /*case 'reimport-statistics-data':
                $db = Zend_Registry::get('db');
                $db->query('TRUNCATE TABLE `Statistics`');
                $this->view->stats = $db->query('SELECT DISTINCT(indicator) as indicator FROM `Stats` WHERE parent != 0 ORDER BY indicator')->fetchAll();
                break;
            case 'fix-empty-statistics-data':
                $db = Zend_Registry::get('db');
                $stats = $db->query('SELECT DISTINCT(indicator) as indicator FROM `Stats` WHERE parent != 0 ORDER BY indicator')->fetchAll();
                $empty_stats = array();
                
                foreach ($stats as $stat) {
                    $row = $db->query('SELECT COUNT(*) as count FROM `Statistics` WHERE indicator = "'.$stat['indicator'].'"')->fetchObject();
                    if ($row->count == 0) {
                        $empty_stats[] = $stat;
                    }
                }
                
                $this->view->stats = $empty_stats;
                break;*/
        }
        
        $this->view->task = $task;
    }
    
    public function reindexOneStorySearchAction()
    {
        $id = (int)$this->_getParam('id');
        
        if ($id > 0) {
            $story_model = $this->__getModel('Story');
            $location_model = $this->__getModel('Location');

            $stories = $story_model->getStories(array('fields' => array('id','name','description','meta_description','tags','keywords','location'),'id' => $id));
            $story = $stories[0];
            
            $locations = $location_model->getLocations(array('id' => $story['location'],'city_name' => 1,'country_name' => 1,'continent_name' => 1));
            $location = (isset($locations[0])) ? $locations[0] : array();
    
            $index = Zend_Search_Lucene::open('search/stories_index');
            $this->_addStoryToSearchIndex($index,$story,$location);
            
            die(true);
        }
        
        die();
    }
    
    /*public function reimportOneStatDataAction()
    {
        $indicator = $this->_getParam('indicator','');
        
        if ($indicator != '') {
            $statistics_model = $this->__getModel('Statistics');
            $country_model = $this->__getModel('Country');
            $result = $country_model->getCountries(array('fields' => array('iso2')));
            $countries = array();
            
            foreach ($result as $res) {
                $countries[$res['iso2']] = array('total' => 0,'num' => 0);
            }
            
            $world_bank_data = array();
            $page = 1;
            $mrv = 10;
            $completed = false;

            while (!$completed) {
                try {
                    $data = @Zend_Json::decode(file_get_contents('http://api.worldbank.org/countries/indicators/'.$indicator.'?page='.$page.'&per_page=1000&MRV='.$mrv.'&format=json'));
                    (!is_null($data[1])) ? $world_bank_data = array_merge_recursive($world_bank_data,$data[1]) : '';
                    ($data[0]['page'] != $data[0]['pages']) ? $page++ : $completed = true;
                }
                catch (Exception $e) {
                    $completed = true;
                }
            }

            foreach ($world_bank_data as $data) {
                if (isset($countries[$data['country']['id']]) && !is_null($data['value']) && $data['value'] != 0) {
                    $countries[$data['country']['id']]['total'] += $data['value'];
                    $countries[$data['country']['id']]['num']++;
                }
            }

            foreach ($countries as $k => $v) {
                if ($v['total'] != 0) {
                    $statistics_model->insert(array('indicator' => $indicator,'country' => $k,'average' => ($v['total'] / $v['num'])));
                }
            }
            
            die(true);
        }
        
        die();
    }*/
    
    public function resizeImageAction()
    {
        $image = $this->_getParam('image','');
        $width = (int)$this->_getParam('width');
        $height = (int)$this->_getParam('height');
        
        $image_path = ROOT_PATH.'/public'.$image;
        
        $resize = new App_Image_Resize($image_path);
        $resize->resizeImage($width,$height,'crop');
        $resize->saveImage($image_path,100);
        
        echo $image;
        die();
    }
    
    protected function _list($content_type,$params = array())
    {
        if (count($params) == 0) {
            $params = $this->_getContentTypeDefaultListParams($content_type);
        }

        if (isset($params['mode']) && $params['mode'] == 'ajax') {
            $paginator_page = ((int)@$params['page'] > 0) ? (int)$params['page'] : 1;
            
            switch ($content_type)
            {
                case 'story':       
                    $this->_getStories(array_merge($params,array('user_names' => 1)));
                    $this->view->stories = $this->_setPaginator($this->view->data,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'category':
                    $category_model = $this->__getModel('Category');
                    $categories = $category_model->getCategories(array_merge($params,array('stories_count' => 1)));
                    $this->view->categories = $this->_setPaginator($categories,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'tag':
                    $tag_model = $this->__getModel('Tag');
                    $tags = $tag_model->getTags(array_merge($params,array('stories_count' => 1,'stories_zero_count' => 1)));
                    $this->view->tags = $this->_setPaginator($tags,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'location':
                    $location_model = $this->__getModel('Location');
                    $locations = $location_model->getLocations(array_merge($params,array('city_name' => 1,'country_name' => 1,'continent_name' => 1,'stories_count' => 1)));
                    $this->view->locations = $this->_setPaginator($locations,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'country':
                    $country_model = $this->__getModel('Country');
                    $countries = $country_model->getCountries(array_merge($params,array('continent_name' => 1,'locations_count' => 1)));
                    $this->view->countries = $this->_setPaginator($countries,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'city':
                    $city_model = $this->__getModel('City');
                    $cities = $city_model->getCities(array_merge($params,array('country_name' => 1,'locations_count' => 1)));
                    $this->view->cities = $this->_setPaginator($cities,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'continent':
                    $continent_model = $this->__getModel('Continent');
                    $continents = $continent_model->getContinents(array_merge($params,array('locations_count' => 1)));
                    $this->view->continents = $this->_setPaginator($continents,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'organisation':
                    $organisation_model = $this->__getModel('Organisation');
                    $organisations = $organisation_model->getOrganisations(array_merge($params,array('organisation_type_name' => 1)));
                    $this->view->organisations = $this->_setPaginator($organisations,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'organisation-type':
                    $organisation_model = $this->__getModel('Organisation');
                    $organisation_types = $organisation_model->getOrganisationTypes($params);
                    $this->view->organisation_types = $this->_setPaginator($organisation_types,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'producer':
                    $producer_model = $this->__getModel('Producer');
                    $producers = $producer_model->getProducers(array_merge($params,array('stories_count' => 1,'stories_zero_count' => 1)));
                    $this->view->producers = $this->_setPaginator($producers,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'media-type':
                    $media_model = $this->__getModel('Media');
                    $medias = $media_model->getMedias(array_merge($params,array('stories_count' => 1)));
                    $this->view->medias = $this->_setPaginator($medias,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'user':
                    $user_admin_model = $this->__getModel('UserAdmin');
                    $user_admins = $user_admin_model->getUserAdmins(array_merge($params,array('user_type_name' => 1)));
                    $this->view->user_admins = $this->_setPaginator($user_admins,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;
                case 'ad':
                    $ad_model = $this->__getModel('Ad');
                    $ads = $ad_model->getAds($params);
                    $this->view->ads = $this->_setPaginator($ads,Zend_Auth::getInstance()->getIdentity()->pagination_items_per_page,$paginator_page);
                    break;        
            }
            
            echo $this->view->render('admin/'.$content_type.'/list-table.phtml');
            die();
        }
        else {
            $params['mode'] = 'ajax';
        }
                
        $this->view->content_type = $content_type;
        $this->view->params = $params;
    }

    protected function _edit($content_type,$params = array())
    {
        $content_id = (int)@$params['id'];
        $fancybox = @$params['fancybox'];
        
        $form = array('id' => $content_id,
                      'type' => $content_type,
                      'params' => array()
                     );
        
        if(!is_null($fancybox)) {
            $form['params']['return'] = ($content_type != 'tag') ? 'id' : 'name';
            $this->_helper->layout->disableLayout();
        }

        switch ($content_type)
        {
            case 'story':
                $this->setUploadify();
                $this->setFancybox();
                $this->setGoogleMaps();
                
                $form['geolocations'] = array();
                
                if ($form['id'] > 0) {
                    $identity = Zend_Auth::getInstance()->getIdentity();
                    $story_model = $this->__getModel('Story');
                    $stories = $story_model->find($form['id']);

                    if ($identity->producer != 0 && $stories[0]->producer != $identity->producer) {
                        $this->_helper->flashMessenger->addMessage(array('type' => 'error','text' => $this->view->i18n->t('error.no_admin_permission')));
                        return $this->_helper->redirector->gotoRoute(array(), 'admin', true, false);
                    }
                    
                    if (isset($stories[0])) {
                        $form = array_merge($form,$stories[0]->toArray());
                        
                        $duration = $form['duration'];
                        $form['duration'] = str_pad(floor($duration / 60),2,'0',STR_PAD_LEFT).':'.str_pad(($duration % 60),2,'0',STR_PAD_LEFT);

                        $geolocation_model = $this->__getModel('GeoLocation');
                        $geolocations = $geolocation_model->getGeoLocations(array('content_id' => $form['id'],'content_type' => 'story'))->toArray();
                        
                        foreach ($geolocations as $k => $v) {
                            $form['geolocations'][] = $v['latlng'];
                        }
                    }
                }
                break;
            case 'category':
                if ($form['id'] > 0) {
                    $category_model = $this->__getModel('Category');
                    $categories = $category_model->find($form['id']);
                    
                    if (isset($categories[0])) {
                        $form = array_merge($form,$categories[0]->toArray());
                    }
                }
                break;
            case 'tag':
                if ($form['id'] > 0) {
                    $tag_model = $this->__getModel('Tag');
                    $tags = $tag_model->find($form['id']);
                    
                    if (isset($tags[0])) {
                        $form = array_merge($form,$tags[0]->toArray());
                    }
                }
                break;
            case 'location':
                if ($form['id'] > 0) {
                    $location_model = $this->__getModel('Location');
                    $locations = $location_model->find($form['id']);
                    
                    if (isset($locations[0])) {
                        $form = array_merge($form,$locations[0]->toArray());
                    }
                }
                break;
            case 'continent':
                if ($form['id'] > 0) {
                    $continent_model = $this->__getModel('Continent');
                    $continents = $continent_model->find($form['id']);

                    if (isset($continents[0])) {
                        $form = array_merge($form,$continents[0]->toArray());
                    }
                }
                break;
            case 'country':
                if ($form['id'] > 0) {
                    $country_model = $this->__getModel('Country');
                    $countries = $country_model->find($form['id']);

                    if (isset($countries[0])) {
                        $form = array_merge($form,$countries[0]->toArray());
                    }
                }
                break;
            case 'city':
                if ($form['id'] > 0) {
                    $city_model = $this->__getModel('City');
                    $cities = $city_model->find($form['id']);

                    if (isset($cities[0])) {
                        $form = array_merge($form,$cities[0]->toArray());
                    }
                }
                break;
            case 'organisation-type':
                if ($form['id'] > 0) {
                    $organisation_model = $this->__getModel('Organisation');
                    $organisation_types = $organisation_model->getOrganisationTypes(array('id' => $form['id']));
                    
                    if (isset($organisation_types[0])) {
                        $form = array_merge($form,$organisation_types[0]);
                    }
                }
                break;
            case 'media-type':
                if ($form['id'] > 0) {
                    $media_model = $this->__getModel('Media');
                    $medias = $media_model->find($form['id']);
                    
                    if (isset($medias[0])) {
                        $form = array_merge($form,$medias[0]->toArray());
                    }
                }
                break;
            case 'organisation':
                $this->setGoogleMaps();
                
                $form['geolocations'] = array();
                
                if ($form['id'] > 0) {
                    $organisation_model = $this->__getModel('Organisation');
                    $organisations = $organisation_model->find($form['id']);
                    
                    if (isset($organisations[0])) {
                        $form = array_merge($form,$organisations[0]->toArray());
                        $geolocation_model = $this->__getModel('GeoLocation');
                        $geolocations = $geolocation_model->getGeoLocations(array('content_id' => $form['id'],'content_type' => 'organisation'))->toArray();
                        
                        foreach ($geolocations as $k => $v) {
                            $form['geolocations'][] = $v['latlng'];
                        }
                    }
                }
                break;
            case 'producer':
                $organisation_model = $this->__getModel('Organisation');
                $this->view->organisation_types = $organisation_model->getOrganisationTypes(array('for_producers' => 1));
                
                if ($form['id'] > 0) {
                    $producer_model = $this->__getModel('Producer');
                    $producers = $producer_model->find($form['id']);
                    
                    if (isset($producers[0])) {
                        $form = array_merge($form,$producers[0]->toArray());
                    }
                }
                break;
            case 'user':
                $user_type_model = $this->__getModel('UserType');   
                $user_admin_model = $this->__getModel('UserAdmin');
                
                if ($form['id'] > 0) {
                    $user_admins = $user_admin_model->find($form['id']);

                    if (isset($user_admins[0])) {
                        $form = array_merge($form,$user_admins[0]->toArray());
                    }
                }
                    
                $this->view->user_types = $user_type_model->getUserTypes();
                break; 
            case 'ad':
                $this->setUploadify();
                
                if ($form['id'] > 0) {
                    $ad_model = $this->__getModel('Ad');
                    $ads = $ad_model->find($form['id']);
                
                    if (isset($ads[0])) {
                        $form = array_merge($form,$ads[0]->toArray());
                    }
                }
                break;            
        }
        
        $this->view->fancybox = $fancybox;
        $this->view->form = $form;
    }

    protected function _validate($content_type,$params = array())
    {
        $params = $this->_getAllParams();
        $result = true;
        
        switch ($content_type)
        {
            case 'organisation-type':
                    if(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                break;
            case 'producer':
                    if(!isset($params['organisation_type']) || (int)$params['organisation_type'] == 0) {
                        $result = $this->view->i18n->t('error.empty_organisation_type');
                    }
                    elseif(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                break;   
            case 'media-type':
                    if(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                break;
            case 'category':
                    if(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                break;
            case 'tag':
                    $tag_model = $this->__getModel('Tag');
                    if(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                break;
            case 'location':
                    if(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                    elseif(trim($params['continent']) == '') {
                        $result = $this->view->i18n->t('error.empty_continent');
                    }
                    elseif(trim($params['country']) == '') {
                        $result = $this->view->i18n->t('error.empty_country');
                    }
                break;
            case 'country':             
                    if(trim($params['continent']) == '') {
                        $result = $this->view->i18n->t('error.empty_continent');
                    }
                    elseif(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                break;
            case 'city':
                    if(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                    elseif(trim($params['country']) == '') {
                        $result = $this->view->i18n->t('error.empty_country');
                    }
                break;
            case 'continent':
                    if(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                break;
            case 'story':
                    if(trim($params['date']) == '') {
                        $result = $this->view->i18n->t('error.empty_date');
                    }
                    elseif (count(($date_parts = explode("-",$params['date']))) != 3 || !checkdate($date_parts[1],$date_parts[2],$date_parts[0])) {
                        $result = $this->view->i18n->t('error.invalid_date');
                    }
                    elseif(trim($params['duration']) == '') {
                        $result = $this->view->i18n->t('error.empty_duration');
                    }
                    elseif (count(($duration_parts = explode(":",$params['duration']))) != 2 || $duration_parts[1] > 59) {
                        $result = $this->view->i18n->t('error.invalid_duration');
                    }
                    elseif(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_title');
                    }
                    elseif(trim($params['url_title']) == '') {
                        $result = $this->view->i18n->t('error.empty_url_title');
                    }
                    elseif(trim($params['description']) == '') {
                        $result = $this->view->i18n->t('error.empty_description');
                    }
                    /*elseif(trim($params['keywords']) == '') {
                        $result = $this->view->i18n->t('error.empty_keywords');
                    }*/
                    elseif(!isset($params['tags'][0]) || (isset($params['tags'][0]) && $params['tags'][0] == 'null')) {
                        $result = $this->view->i18n->t('error.select_at_least_one_tag');
                    }
                    elseif(count($params['geolocation']) == 0) {
                        $result = $this->view->i18n->t('error.empty_geolocation');
                    }
                break;
            case 'organisation':
                    if(trim($params['name']) == '') {
                        $result = $this->view->i18n->t('error.empty_name');
                    }
                    elseif(count($params['geolocation']) == 0) {
                        $result = $this->view->i18n->t('error.empty_geolocation');
                    }
                 break;
            case 'user':
                $user_admin_model = $this->__getModel('UserAdmin');

                if(trim($params['name']) == '') {
                    $result = $this->view->i18n->t('error.empty_name');
                }
                elseif(trim($params['username']) == '') {
                    $result = $this->view->i18n->t('error.empty_username');
                }
                elseif ($user_admin_model->checkUsernameExists($params['id'],strtolower(trim($params['username'])))) {
                    $result = $this->view->i18n->t('error.username_exists');
                }
                elseif($params['password'] != '') {
                    if (strlen($params['password']) < 6) {
                        $result = $this->view->i18n->t('error.password_min_length');
                    }
                    elseif ($params['password'] != $params['password_repeat']) {
                        $result = $this->view->i18n->t('error.password_dont_match');
                    }
                }
                break; 
            case 'ad':
                if(trim($params['name']) == '') {
                    $result = $this->view->i18n->t('error.empty_name');
                }
                elseif(trim($params['url']) == '') {
                    $result = $this->view->i18n->t('error.empty_url');
                }
                elseif(trim($params['image']) == '') {
                    $result = $this->view->i18n->t('error.please_upload_image');
                }
                break;        
        }
        
        echo $result;
        die();
    }
    
    protected function _save($content_type,$params = array())
    {
        $db = Zend_Registry::get('db');
        $request = $this->getRequest();
        $content_id = (int)$request->getParam('id');

        if ($request->isPost()) {
            
            switch ($content_type)
            {
                case 'organisation-type':
                        $data = array('name' => $request->getParam('name',''));
                    break;
                case 'producer':
                        $data = array('organisation_type' => (int)$request->getParam('organisation_type'),
                                      'name' => $request->getParam('name',''),
                                      'url' => $request->getParam('url',''),
                                      'twitter_url' => $request->getParam('twitter_url',''),
                                      'facebook_url' => $request->getParam('facebook_url',''),
                                      'meta_description' => $request->getParam('meta_description',''),
                                     );
                    break;
                case 'media-type':
                        $data = array('name' => $request->getParam('name',''));
                    break;
                case 'category':
                        $data = array('name' => $request->getParam('name',''));
                    break;
                case 'location':
                        $data = array('name' => $request->getParam('name',''),
                                      'continent' => (int)$request->getParam('continent'),
                                      'country' => (int)$request->getParam('country'),
                                      'city' => (int)$request->getParam('city')
                                     );
                    break;
                case 'country':
                        $data = array('name' => $request->getParam('name',''),
                                      'continent' => (int)$request->getParam('continent'),
                                      'iso2' => strtoupper($request->getParam('iso2','')),
                                      'iso3' => strtoupper($request->getParam('iso3','')),
                                     );
                    break;
                case 'city':
                        $data = array('name' => $request->getParam('name',''),
                                      'country' => (int)$request->getParam('country')
                                     );
                    break;
                case 'continent':
                        $data = array('name' => $request->getParam('name',''),
                                      'continent_code' => $request->getParam('continent_code','')
                                     );
                    break;
                case 'tag':
                        $data = array('name' => trim($request->getParam('name','')));
                        $data['slug'] = App_Filter_Output::stringURLSafe($data['name']);
                        
                        $tag_model = $this->__getModel('Tag');
                        $i = 1;
                        $slug = $data['slug'];
                        while ($tag_model->checkSlugExists($data['slug'],$content_id)) { $i++;
                            $data['slug'] = $slug.'-'.$i;
                        }
                        
                        if ($content_id > 0) {
                            $tags = $tag_model->getTags(array('fields' => array('slug'),'id' => $content_id));
                            if (isset($tags[0]) && $tags[0]['slug'] != $data['slug']) {
                                $db->query('UPDATE `Story` SET tags = REPLACE(tags,";'.$tags[0]['slug'].';",";'.$data['slug'].';") WHERE 1');
                            }
                        }
                    break;
                case 'organisation':
                        $geolocation_model = $this->__getModel('GeoLocation');
                        
                        if ($content_id > 0) {
                             $geolocation_model->delete("content_id = '".$content_id."' AND content_type = 'organisation'");
                        }
                        
                        $geolocations = $request->getParam('geolocation',array());
                        foreach ($geolocations as $k => $latlng) {
                            $geolocation_data = array('content_id' => $content_id,'content_type' => 'organisation','latlng' => $latlng);
                            ($k == 0) ? $geolocation_data['primary'] = 1 : '';
                            $geolocation_model->insert($geolocation_data);
                        }
            
                        $data = array('organisation_type' => (int)$request->getParam('organisation_type'),
                                      'name' => $request->getParam('name',''),
                                      'website_url' => $request->getParam('website_url',''),
                                      'facebook_url' => $request->getParam('facebook_url',''),
                                      'donate_url' => $request->getParam('donate_url',''),
                                      'location' => (int)$request->getParam('location')
                                     );
                    break;
                case 'user':

                    $producer_id = $request->getParam('producer');
                    $user_type = $request->getParam('user_type');

                    if ( $user_type == 1 ) {
                        $producer_id = 0;
                    }

                    $data = array('user_type' => $user_type,
                                  'producer' => $producer_id,
                                  'name' => trim($request->getParam('name','')),
                                  'username' => strtolower(trim($request->getParam('username','')))
                                 );
                                     
                    $password = $request->getParam('password','');
                    if ($password != '') {
                        $data['password'] = md5($password);
                    }
                                     
                    $identity = Zend_Auth::getInstance()->getIdentity();         
                    if ($content_id == $identity->id) {
                        $identity->name = $data['name'];
                        $identity->username = $data['username'];
                    }            
                    break;
                case 'ad':
                    $data = array('name' => trim($request->getParam('name','')),
                                  'url' => trim($request->getParam('url','')),
                                  'image' => trim($request->getParam('image','')),
                                  'state' => (int)$request->getParam('state')
                                  );      
                    break;          
            }

            $table_name = $this->_getContentTypeTableName($content_type);

            if ($content_id > 0) {
                $db->update($table_name,$data,'id='.$content_id);
                $this->_log_admin(array('content_type' => $content_type,'action' => 'updated','info' => $data['name']));
            }
            else {
                $db->insert($table_name,$data);
                $content_id = $db->lastInsertId($table_name,'id');
                $this->_log_admin(array('content_type' => $content_type,'action' => 'created','info' => $data['name']));
            }
           
            if (isset($params['return'])) {
                echo ($params['return'] == 'id') ? $content_id : $data[$params['return']];
                die();
            }
        }
        
        $route_params = array('content_type' => $content_type,'content_action' => 'list');
        if ($request->getParam('add_another','0') == '1') {
            $route_params['content_action'] = 'edit';
        }
        else {
            $this->_helper->flashMessenger->addMessage(array('type' => 'success','text' => $this->view->i18n->t('content.type_'.$content_type).' "'.$data['name'].'" <b>'.$this->view->i18n->t('success.successfully_saved').'</b>'));
        }
        return $this->_helper->redirector->gotoRoute($route_params, 'admin_content', true, false);
    }
    
    protected function _validate_delete($content_type,$params = array())
    {
        $request = $this->getRequest();
        $ids = array_keys($request->getParam('check_list',array()));
        
        if (count($ids) > 0) {
            $db = Zend_Registry::get('db');
            $table = $this->_getContentTypeTableName($content_type);
            $info = array();
            
            foreach ($ids as $k => $v) {
                $rows = $db->query("SELECT name FROM `".$table."` WHERE id = ".$v)->fetchAll();
                $info[$v] = array('name' => $rows[0]['name']);
            }
            
            $check_dependances = array('category' => array('story' => 'category'),
                                       'location' => array('story' => 'location'),
                                       'organisation-type' => array('story' => 'organisation_type','organisation' => 'organisation_type'),
                                       'producer' => array('story' => 'producer'),
                                       'media-type' => array('story' => 'media'),
                                       'country' => array('city' => 'country'),
                                       'continent' => array('country' => 'continent')
                                      );
            $dependances = array();
            if (isset($check_dependances[$content_type])) {
                foreach ($check_dependances[$content_type] as $dependant_content_type => $join_field) {
                    $join_table = $this->_getContentTypeTableName($dependant_content_type);
                    foreach ($ids as $k => $v) {
                        $rows = $db->query("SELECT COUNT(".$join_table.".id) as count FROM `".$table."` LEFT JOIN `".$join_table."` ON ".$join_table.".".$join_field." = ".$table.".id WHERE ".$table.".id = ".$v)->fetchAll();
                        if ($rows[0]['count'] > 0) {
                            if (!isset($info[$v]['dependances'])) {
                                $info[$v]['dependances'] = array();
                            }
                            $info[$v]['dependances'][] = strtolower($this->view->i18n->t('content.type_'.$dependant_content_type.'s'));
                        }
                    }
                }
            }
            echo Zend_Json::encode($info);
        }
        
        die();
    }
    
    protected function _delete($content_type,$params = array())
    {
            $identity = Zend_Auth::getInstance()->getIdentity();
            $request = $this->getRequest();
            
            if ($request->isPost()) {
                $ids = array_keys($request->getParam('check_list',array()));
                
                if (count($ids) > 0) {
                    $db = Zend_Registry::get('db');
                    $table = $this->_getContentTypeTableName($content_type);
                    $where = $db->quoteInto('id IN (?)',$ids);
                    
                    if ($identity->producer != 0) {
                        $where .= ' AND producer = '.$identity->producer;
                    }

                    $db->delete($table,$db->quoteInto('id IN (?)',$ids));
                    $this->_log_admin(array('content_type' => $content_type,'action' => 'deleted','info' => count($ids)));
                }
            }
    
            die();
    }
    
    protected function _report($content_type,$params = array())
    {
        $params = array('fields' => array('id','name','popularity'),
                        'sort_by' => 'popularity',
                        'order' => 'DESC',
                        'limit' => $this->view->i18n->t('reports.popularity_limit')
                       );
        
        switch ($content_type) {
            case 'story':
                $story_model = $this->__getModel('Story');
                $items = $story_model->getStories($params);
                break;
            case 'category':
                $category_model = $this->__getModel('Category');
                $items = $category_model->getCategories($params);
                break;  
            case 'tag':
                $tag_model = $this->__getModel('Tag');
                $items = $tag_model->getTags($params);
                break;
            case 'media-type':
                $media_model = $this->__getModel('Media');
                $items = $media_model->getMedias($params);
                break;
        }

        $filename = $this->view->i18n->t('menu.reports').'_'.$this->view->i18n->t('content.type_'.$content_type).'_'.$this->view->i18n->t('reports.popularity').'_'.date("d_m_Y",time()).'.xls';
        $filename = str_replace(' ','_',$filename);
        
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");;
        header("Content-Disposition: attachment;filename=".$filename);
        header("Content-Transfer-Encoding: binary ");
        
        $xls = new App_Xls();
        $xls->xlsBOF();

        $j = 0;
        foreach ($params['fields'] as $v) {
            $xls->xlsWriteLabel(0,$j,$v);
            $j++;
        }
        
        $i = 1;
        foreach ($items as $item) { 
            $j = 0;
            foreach ($item as $v) {
                $xls->xlsWriteLabel($i,$j,$v);
                $j++;
            }
            $i++;
        }
        
        $xls->xlsEOF();
        die();
    }
}

?>