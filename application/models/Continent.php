<?php
class Continent extends Zend_Db_Table_Abstract
{
    protected $_name = 'Continent';
    
    public function getContinents($filter = array())
    {
        $select = $this->select();
        
        if (isset($filter['id'])) {
            $select->where('id = (?)',$filter['id']);
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
		if (isset($filter['locations_count'])) {
			foreach ($result as $k => $v) {
				$select = $this->select()->setIntegrityCheck(false)->from('Location',array('count' => 'COUNT(*)'));
				$select->where('continent = (?)',$v['id']);
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
