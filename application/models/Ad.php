<?php
class Ad extends Zend_Db_Table_Abstract
{
    protected $_name = 'Ad';
    
    public function getAds($filter = array())
    {
    	$select = $this->select();
    		
        if (isset($filter['search']) && trim($filter['search']) != '') {
        	$select->where('name LIKE (?)','%'.trim($filter['search']).'%')->orWhere('url LIKE (?)','%'.trim($filter['search']).'%');
        }
        if (isset($filter['state'])) {
        	$select->where('state = (?)',$filter['state']);
        }
        
       	if (isset($filter['sort_by']) && isset($filter['order']) && in_array($filter['sort_by'],$this->info(Zend_Db_Table_Abstract::COLS))) {
   			$select->order($filter['sort_by'].' '.$filter['order']);
        }
        else {
            $select->order('name');
        }
        
        return $this->fetchAll($select);
    }
    
    public function getRandomAd($filter = array())
    {
    	$select = $this->select();
    	
    	if (isset($filter['state'])) {
    		$select->where('state = (?)',$filter['state']);
    	}
    	
    	$select->order('RAND()');
    	
    	return $this->fetchRow($select);
    }
}
?>
