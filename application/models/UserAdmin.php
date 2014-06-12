<?php
class UserAdmin extends Zend_Db_Table_Abstract
{
    protected $_name = 'UserAdmin';
    
    public function getUserAdmins($filter = array())
    {
    	$select = $this->select()->setIntegrityCheck(false)->from(array('ua' => $this->_name));
    		
        if (isset($filter['search']) && trim($filter['search']) != '') {
        	$select->where('ua.name LIKE (?)','%'.trim($filter['search']).'%')->orWhere('ua.username LIKE (?)','%'.trim($filter['search']).'%');
        }
        if (isset($filter['user_type_name'])) {
        	$select->joinLeft(array('ut' => 'UserType'),'ut.id = ua.user_type',array('user_type_name' => 'name'));
        }
        
       	if (isset($filter['sort_by']) && isset($filter['order']) && in_array($filter['sort_by'],$this->info(Zend_Db_Table_Abstract::COLS))) {
            if ($filter['sort_by'] == 'user_type' && isset($filter['user_type_name'])) {
           		$select->order('ut.name '.$filter['order']);
            }
            else {
       			$select->order('ua.'.$filter['sort_by'].' '.$filter['order']);
            }
        }
        else {
            $select->order('name');
        }
        
        return $this->fetchAll($select);
    }
    
    public function checkUsernameExists($id,$username) 
    {
    		$select = $this->select()->where('id <> (?)',$id)->where('username = (?)',$username);
    		$result = $this->fetchRow($select);
    		
    		return (!is_null($result)) ? true : false;
    }
}
?>
