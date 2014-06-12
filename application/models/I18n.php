<?php
class I18n extends Zend_Db_Table_Abstract
{
    protected $_name = 'I18n';
    protected $_translations = array();
    
    public function init()
    {
       $result = $this->fetchAll();
       foreach ($result as $res) {
           $this->_translations[$res->tkey] = $res->value;
       }
    }

    public function t($tkey)
    {
        return (isset($this->_translations[$tkey])) ? $this->_translations[$tkey] : $tkey;
    }
}
?>
