<?php
class User extends Zend_Db_Table_Abstract
{
    protected $_name = 'User';
    
    public function getUsers($filter = array())
    {
        $select = $this->select()->setIntegrityCheck(false);

        if(isset($filter['fields']) && is_array($filter['fields'])) {
        	$select->from($this->_name,$filter['fields']);
        }
        else {
        	$select->from($this->_name);
        }

    	if (isset($filter['id']) && (int)$filter['id'] > 0) {
    		$select->where('id = (?)',(int)$filter['id']);
    	}
        if (isset($filter['fb_user_id']) && (int)$filter['fb_user_id'] > 0) {
    		$select->where('fb_user_id = (?)',(int)$filter['fb_user_id']);
    	}
    		
    	return $this->fetchAll($select);
    }
}
?>
