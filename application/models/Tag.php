<?php
class Tag extends Zend_Db_Table_Abstract
{
    protected $_name = 'Tag';

    public function getTags($filter = array())
    {
      	$select = $this->select()->setIntegrityCheck(false);
       
        if(isset($filter['fields']) && is_array($filter['fields'])) {
        	$select->from($this->_name,$filter['fields']);
        }
        else {
        	$select->from($this->_name);
        }

        if (isset($filter['id']) && (int)$filter['id'] > 0) {
        	$select->where('id = (?)',(int)$filter['id']);
        }
        if (isset($filter['name'])) {
            $select->where('name = (?)',$filter['name']);
        }
        if (isset($filter['search']) && trim($filter['search']) != '') {
        		$select->where('name LIKE (?)','%'.trim($filter['search']).'%');
        }
        if (isset($filter['sort_by']) && isset($filter['order']) && in_array($filter['sort_by'],$this->info(Zend_Db_Table_Abstract::COLS))) {
            $select->order($filter['sort_by'].' '.$filter['order']);
        }
        else {
            $select->order('name');
        }
        
		$result = $this->fetchAll($select)->toArray();
		
		if (isset($filter['stories_count'])) {
			foreach ($result as $k => $v) {
				@Zend_Loader::loadClass('Story');
				$story_model = new Story();
				$stories_filter = array('fields' => array('count' => 'COUNT(*)'),'tags' => $v['slug']);
				
				if (isset($filter['stories_filter']) && is_array($filter['stories_filter'])) {
					$stories_filter = array_merge($filter['stories_filter'],$stories_filter);	
				}
				$stories = $story_model->getStories($stories_filter);
					
				if ($stories[0]['count'] > 0 || isset($filter['stories_zero_count'])) {
					$result[$k]['stories_count'] = $stories[0]['count'];
				}
				else {
					unset($result[$k]);
				}
			}
		}
		if (isset($filter['sort_by']) && isset($filter['order']) && $filter['sort_by'] == 'stories_count') {
		    $sorter = new App_Sort('stories_count');
		    $result = $sorter->sort($result,$filter['order']);
		}

        return $result;
    }
    
    public function checkSlugExists($slug,$instead = NULL)
    {
		$select = $this->select()->where('slug = (?)',$slug);
		
		if (!is_null($instead) && (int)$instead > 0) {
			$select->where('id <> (?)',$instead);	
		}
		
		$row = $this->fetchRow($select);
		return (!is_null($row)) ? true : false;
    }

}
?>
