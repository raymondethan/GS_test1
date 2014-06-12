<?php
class Statistics extends Zend_Db_Table_Abstract
{
    protected $_name = 'Statistics';

    public function getStatistics($filter = array())
    {
        $select = $this->select()->setIntegrityCheck(false);
        
        if(isset($filter['fields']) && is_array($filter['fields'])) {
        	$select->from($this->_name,$filter['fields']);
        }
        else {
        	$select->from($this->_name);
        }

        if (isset($filter['indicator'])) {
            $select->where('indicator = (?)',$filter['indicator']);
        }
        if (isset($filter['country'])) {
        	$select->where('country = (?)',$filter['country']);
        }
        if (isset($filter['countries_not']) && is_array($filter['countries_not']) && count($filter['countries_not']) > 0) {
        	$select->where('country NOT IN (?)',$filter['countries_not']);
        }
        
        if (isset($filter['random_values'])) {
        	$select->order('RAND()');
        }
        
        if (isset($filter['limit'])) {
        	$select->limit($filter['limit']);
        }
        
        return $this->fetchAll($select);
    }
}
?>
