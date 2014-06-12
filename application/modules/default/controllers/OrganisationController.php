<?php

class OrganisationController extends App_Controller_Action
{
	
    protected $controller_name = 'organisation';
	
    public function indexAction()
    {
        $filter = array('sort_by' => (!is_null($this->_getParam('sort_by'))) ? strtolower($this->_getParam('sort_by')) : 'name',
                        'order' => (strtolower($this->_getParam('order')) == 'desc') ? 'DESC' : 'ASC'
                        );
        
        $organisation_model = $this->__getModel('Organisation');
        $organisations = $organisation_model->getOrganisations($filter);
        $this->view->data= $organisations->toArray();
    }
}

?>