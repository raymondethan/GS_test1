<?php
class Location extends Zend_Db_Table_Abstract
{
    protected $_name = 'Location';
    
    public function getLocations($filter = array())
    {
    	$select = $this->select()->setIntegrityCheck(false);
        
        if(isset($filter['fields']) && is_array($filter['fields'])) {
        	$select->from(array('l' => $this->_name),$filter['fields']);
        }
        else {
        	$select->from(array('l' => $this->_name));
        }
        
        if (isset($filter['city_name'])) {
        	$select->joinLeft(array('cit' => 'City'),'cit.id = l.city',array('city_name' => 'name'));
        }
        if (isset($filter['country_iso2'])) {
        	$select->joinLeft(array('cou' => 'Country'),'cou.id = l.country',array('country_iso2' => 'iso2'));
        }
        if (isset($filter['country_name'])) {
        	$select->joinLeft(array('cou' => 'Country'),'cou.id = l.country',array('country_name' => 'name'));
        }
        if (isset($filter['continent_code'])) {
        	$select->joinLeft(array('con' => 'Continent'),'con.id = l.continent',array('continent_code' => 'continent_code'));
        }
        if (isset($filter['continent_name'])) {
        	$select->joinLeft(array('con' => 'Continent'),'con.id = l.continent',array('continent_name' => 'name'));
        }
        
        if (isset($filter['id'])) {
        	$select->where('l.id = (?)',(int)$filter['id']);
        }
        if (isset($filter['search']) && trim($filter['search']) != '') {
        	$select->where('l.name LIKE (?)','%'.trim($filter['search']).'%');
        }
        if (isset($filter['country']) && (int)$filter['country'] > 0) {
        	$select->where('l.country = (?)',(int)$filter['country']);
        }
        if (isset($filter['city']) && (int)$filter['city'] > 0) {
        	$select->where('l.city = (?)',(int)$filter['city']);
        }
        if (isset($filter['continent']) && (int)$filter['continent'] > 0) {
        	$select->where('l.continent = (?)',(int)$filter['continent']);
        }

        $sort_fields = array('name' => 'l.name',
        					 'city' => 'cit.name',
        					 'country' => 'cou.name',
        					 'continent' => 'con.name');
        if (isset($filter['sort_by']) && isset($filter['order']) && isset($sort_fields[$filter['sort_by']])) {
        	$select->order($sort_fields[$filter['sort_by']].' '.$filter['order']);
        }
        else {
        	$select->order('l.name');
        }
        
        $result = $this->fetchAll($select)->toArray();
		if (isset($filter['stories_count'])) {
			foreach ($result as $k => $v) {
				$select = $this->select()->setIntegrityCheck(false)->from('Story',array('count' => 'COUNT(*)'));
				$select->where('location = (?)',$v['id']);
				$row = $this->fetchRow($select);
				$result[$k]['stories_count'] = $row->count;
			}
		}
		if (isset($filter['sort_by']) && isset($filter['order']) && $filter['sort_by'] == 'stories_count') {
		    $sorter = new App_Sort('stories_count');
		    $result = $sorter->sort($result,$filter['order']);
		}
		
        return $result;
    }
}
?>
