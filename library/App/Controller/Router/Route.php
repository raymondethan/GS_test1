<?php 
require_once 'Zend/Controller/Router/Route/Abstract.php';
class App_Controller_Router_Route extends Zend_Controller_Router_Route
{
    public function getValues()
    {
        return $this->_values;
    }
}
?>