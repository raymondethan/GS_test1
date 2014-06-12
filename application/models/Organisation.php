<?php
class Organisation extends Zend_Db_Table_Abstract
{
    protected $_name = 'Organisation';

    public function getOrganisations($filter = array())
    {
        $select = $this->select()->setIntegrityCheck(false)->from(array('o' => $this->_name));
        
        if (isset($filter['organisation_type_name'])) {
        	$select->joinLeft(array('ot' => 'OrganisationType'),'ot.id = o.organisation_type',array('organisation_type_name' => 'name'));
        }
        
        if (isset($filter['search']) && trim($filter['search']) != '') {
        	$select->where('o.name LIKE (?)','%'.trim($filter['search']).'%');
        }
		if (isset($filter['sort_by']) && isset($filter['order']) && $filter['sort_by'] == 'organisation_type') {
			$select->order('ot.name '.$filter['order']);
		}
        elseif (isset($filter['sort_by']) && isset($filter['order']) && in_array($filter['sort_by'],$this->info(Zend_Db_Table_Abstract::COLS))) {
            $select->order('o.'.$filter['sort_by'].' '.$filter['order']);
        }
        else {
            $select->order('o.name');
        }

        return $this->fetchAll($select);
    }
    
    public function getOrganisationTypes($filter = array())
    {
        $select = $this->select()->setIntegrityCheck(false)->from('OrganisationType');
        
        if (isset($filter['id'])) {
            $select->where('id = (?)',$filter['id']);
        }
       	if (isset($filter['for_producers'])) {
            $select->where('for_producers = (?)',$filter['for_producers']);
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
		
        return $this->fetchAll($select);
    }
}
?>
