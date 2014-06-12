<?php

class MediaController extends App_Controller_Action
{
	
    protected $controller_name = 'media';
	
    public function indexAction()
    {
        $filter = array('sort_by' => (!is_null($this->_getParam('sort_by'))) ? strtolower($this->_getParam('sort_by')) : 'name',
                        'order' => (strtolower($this->_getParam('order')) == 'desc') ? 'DESC' : 'ASC'
                        );
        
        $media_model = $this->__getModel('Media');
        $medias = $media_model ->getMedias($filter);
        $this->view->data= $medias->toArray();
    }
}

?>