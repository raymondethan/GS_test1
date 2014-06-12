<?php
class Category extends Zend_Db_Table_Abstract
{
    protected $_name = 'Category';

    public function getCategories($filter = array())
    {
       	$select = $this->select()->setIntegrityCheck(false);
       
        if(isset($filter['fields']) && is_array($filter['fields'])) {
        	$select->from($this->_name,$filter['fields']);
        }
        else {
        	$select->from($this->_name);
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
        
        if (isset($filter['limit'])) {
        	$select->limit($filter['limit']);
        }
        
        $result = $this->fetchAll($select)->toArray();
        
		if (isset($filter['stories_count'])) {
			foreach ($result as $k => $v) {
				$select = $this->select()->setIntegrityCheck(false)->from('Story',array('count' => 'COUNT(*)'));
				$select->where('category = (?)',$v['id']);
				$row = $this->fetchRow($select);
				$result[$k]['stories_count'] = $row->count;
			}
		}
		if (isset($filter['sort_by']) && isset($filter['order']) && $filter['sort_by'] == 'stories_count') {
		    $sorter = new App_Sort($filter['sort_by']);
		    $result = $sorter->sort($result,$filter['order']);
		}

        return $result;
    }
}
?>
