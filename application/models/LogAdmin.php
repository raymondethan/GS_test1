<?php
class LogAdmin extends Zend_Db_Table_Abstract
{
    protected $_name = 'LogAdmin';

    public function getAdminLogs($limit = NULL)
    {
        $select = $this->select()->setIntegrityCheck(false)->from(array('l' => $this->_name));
        $select->joinLeft(array('u' => 'UserAdmin'),'u.id = l.user',array('user_name' => 'name'));

        if (!is_null($limit)) {
        		$select->limit($limit);
        }
        
        $select->order('l.date DESC');

        return $this->fetchAll($select);
    }
}
?>
