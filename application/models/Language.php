<?php
class Language extends Zend_Db_Table_Abstract
{
    protected $_name = 'Language';
    
    public function getLanguages()
    {
    	$select = $this->select()->order('order');
    	return $this->fetchAll($select);
    }
}
?>
