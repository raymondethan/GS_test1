<?php

class TagController extends App_Controller_Action
{
	
    protected $controller_name = 'tag';
	
    public function indexAction()
    {
        $filter = array('sort_by' => (!is_null($this->_getParam('sort_by'))) ? strtolower($this->_getParam('sort_by')) : 'name',
                        'order' => (strtolower($this->_getParam('order')) == 'desc') ? 'DESC' : 'ASC'
                        );
        
        $tag_model = $this->__getModel('Tag');
        $tags = $tag_model->getTags($filter);
        $this->view->data= $tags->toArray();
    }

}

?>