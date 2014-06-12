<?php

class LocationController extends App_Controller_Action
{
	
    protected $controller_name = 'location';
	
    public function indexAction()
    {
        $location_model = $this->__getModel('Location');
        $this->view->data= $location_model->fetchAll()->toArray();
    }


}

?>