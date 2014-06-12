<?php
class UserTypeAcl extends Zend_Db_Table_Abstract
{
    protected $_name = 'UserTypeAcl';
    
    public function getUserTypeAcls($user_type)
    {
    		$select = $this->select()->where('user_type = (?)',$user_type);

        return $this->fetchAll($select);
    }
}
?>
