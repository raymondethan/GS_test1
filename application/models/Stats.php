<?php
class Stats extends Zend_Db_Table_Abstract
{
    protected $_name = 'Stats';

    public function getStats($filter = array())
    {
    	$select = $this->select();
    	
    	if (isset($filter['parent'])) {
    		$select->where('parent = (?)',$filter['parent']);
    	}
    	if (isset($filter['state'])) {
    		$select->where('state = (?)',$filter['state']);
    	}
    	
    	$select->order('name');
    	
		return $this->fetchAll($select);
    }
}
?>
