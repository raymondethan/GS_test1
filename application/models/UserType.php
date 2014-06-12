<?php
class UserType extends Zend_Db_Table_Abstract
{
    protected $_name = 'UserType';
    
    public function getUserTypes()
    {
    		$select = $this->select()->order('name');

        return $this->fetchAll($select);
    }
}
?>
