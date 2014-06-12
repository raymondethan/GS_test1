<?php
class City extends Zend_Db_Table_Abstract
{
    protected $_name = 'City';
    
    public function getCities($filter = array())
    {
        $select = $this->select()->setIntegrityCheck(false)->from(array('cit' => $this->_name));
        
        if (isset($filter['country_name'])) {
        		$select->joinLeft(array('cou' => 'Country'),'cou.id = cit.country',array('country_name' => 'name'));
        }
        if (isset($filter['locations_count'])) {
        		$select->joinLeft(array('l' => 'Location'),'l.city = cit.id',array('location_id' => 'id'));
        }
        
        if (isset($filter['search']) && trim($filter['search']) != '') {
        		$select->where('cit.name LIKE (?)','%'.trim($filter['search']).'%');
        }
        if (isset($filter['id'])) {
            $select->where('cit.id = (?)',$filter['id']);
        }
        if(isset($filter['country'])) {
            $select->where('cit.country = (?)',$filter['country']);
        }
        
        $sort_fields = array('name' => 'cit.name',
        					 'country' => 'cou.name');
        if (isset($filter['sort_by']) && isset($filter['order']) && isset($sort_fields[$filter['sort_by']])) {
        	$select->order($sort_fields[$filter['sort_by']].' '.$filter['order']);
        }
        else {
        	$select->order('cit.name');
        }
        
        $rows = $this->fetchAll($select)->toArray();
        $result = array();
		if (isset($filter['locations_count'])) {
			foreach ($rows as $k => $v) {
				if (!isset($result[$v['id']])) {
					$v['locations_count'] = 0;
					$result[$v['id']] = $v;
				}
				if (!is_null($v['location_id'])) {
					$result[$v['id']]['locations_count']++;
				}
			}
		}
		else {
			$result = $rows;
		}
		if (isset($filter['sort_by']) && isset($filter['order']) && $filter['sort_by'] == 'locations_count') {
		    $sorter = new App_Sort('locations_count');
		    $result = $sorter->sort($result,$filter['order']);
		}
		
        return $result;
    }
}
?>
