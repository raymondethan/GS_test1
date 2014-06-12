<?php
class Country extends Zend_Db_Table_Abstract
{
    protected $_name = 'Country';
    
    public function getCountries($filter = array())
    {
        $select = $this->select()->setIntegrityCheck(false);
        
        if(isset($filter['fields']) && is_array($filter['fields'])) {
        	$select->from(array('cou' => $this->_name),$filter['fields']);
        }
        else {
        	$select->from(array('cou' => $this->_name));
        }
        
        if (isset($filter['continent_name'])) {
        	$select->joinLeft(array('con' => 'Continent'),'con.id = cou.continent',array('continent_name' => 'name'));
        }
        
        if (isset($filter['id'])) {
            $select->where('cou.id = (?)',$filter['id']);
        }
        if (isset($filter['search']) && trim($filter['search']) != '') {
        	$select->where('cou.name LIKE (?)','%'.trim($filter['search']).'%');
        }
        if(isset($filter['continent'])) {
            $select->where('cou.continent = (?)',$filter['continent']);
        }

        $sort_fields = array('name' => 'cou.name',
        					 'continent' => 'con.name');
        if (isset($filter['sort_by']) && isset($filter['order']) && isset($sort_fields[$filter['sort_by']])) {
        	$select->order($sort_fields[$filter['sort_by']].' '.$filter['order']);
        }
        else {
        	$select->order('cou.name');
        }
        
        $result = $this->fetchAll($select)->toArray();
		if (isset($filter['locations_count'])) {
			foreach ($result as $k => $v) {
				$select = $this->select()->setIntegrityCheck(false)->from('Location',array('count' => 'COUNT(*)'));
				$select->where('country = (?)',$v['id']);
				$row = $this->fetchRow($select);
				$result[$k]['locations_count'] = $row->count;
			}
		}
		if (isset($filter['sort_by']) && isset($filter['order']) && $filter['sort_by'] == 'locations_count') {
		    $sorter = new App_Sort('locations_count');
		    $result = $sorter->sort($result,$filter['order']);
		}
		
        return $result;
    }

}
?>
