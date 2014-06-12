<?php
class Menu extends Zend_Db_Table_Abstract
{
    protected $_name = 'Menu';

    public function getMenus($filter = array())
    {
        $select = $this->select();
        
        if (isset($filter['type'])) {
        	$select->where('type = (?)',$filter['type']);
        }
        if (isset($filter['active'])) {
        	$select->where('active = (?)',$filter['active']);
        }
        
        return $this->fetchAll($select);
    }
}
?>
