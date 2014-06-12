<?php

class CategoryController extends App_Controller_Action
{
	
    protected $controller_name = 'category';
	
    public function indexAction()
    {
        $filter = array('sort_by' => (!is_null($this->_getParam('sort_by'))) ? strtolower($this->_getParam('sort_by')) : 'name',
                        'order' => (strtolower($this->_getParam('order')) == 'desc') ? 'DESC' : 'ASC'
                        );
        
        $category_model = $this->__getModel('Category');
        $categories = $category_model->getCategories($filter);
        $this->view->data= $categories->toArray();
    }

}

?>