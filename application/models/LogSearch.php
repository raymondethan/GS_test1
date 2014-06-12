<?php
class LogSearch extends Zend_Db_Table_Abstract
{
    protected $_name = 'LogSearch';

    public function getLogSearchs($filter = array())
    {
    	$select = $this->select();
    	
    	if (isset($filter['term'])) {
    		$select->where('term = (?)',$filter['term']);
    	}
    	
    	$select->order('popularity DESC');
    	return $this->fetchAll($select);
    }
}
?>
