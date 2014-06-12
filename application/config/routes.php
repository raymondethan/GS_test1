<?php

$router = new Zend_Controller_Router_Rewrite();
 
$router->addRoute('default',
    new Zend_Controller_Router_Route('*', array('controller' => 'home', 'action' => 'index'))
);
$router->addRoute('action',
    new Zend_Controller_Router_Route(':action/:controller/*', array('controller' => 'home', 'action' => 'index'))
);
$router->addRoute('story',
    new Zend_Controller_Router_Route('story/:story_id/:url_title/*', array('controller' => 'story','action' => 'index','url_title' => ''))
);
$router->addRoute('story_save',
     new Zend_Controller_Router_Route('story/save/:story_id/*', array('controller' => 'story','action' => 'save','story_id' => '0'))
);
$router->addRoute('stories',
     new Zend_Controller_Router_Route('stories/:params', array('controller' => 'home','action' => 'index','params' => ''))
);
$router->addRoute('producer',
		new Zend_Controller_Router_Route('producer/:producer_id/*', array('controller' => 'home','action' => 'index'))
);
$router->addRoute('tags',
     new Zend_Controller_Router_Route('tags/*', array('controller' => 'tag', 'action' => 'index'))
);
$router->addRoute('categories',
     new Zend_Controller_Router_Route('categories/*', array('controller' => 'category', 'action' => 'index'))
);
$router->addRoute('medias',
     new Zend_Controller_Router_Route('medias/*', array('controller' => 'media', 'action' => 'index'))
);
$router->addRoute('organisations',
     new Zend_Controller_Router_Route('organisations/*', array('controller' => 'organisation', 'action' => 'index'))
);
$router->addRoute('locations',
     new Zend_Controller_Router_Route('locations/*', array('controller' => 'location', 'action' => 'index'))
);
$router->addRoute('countries',
     new Zend_Controller_Router_Route('countries/*', array('controller' => 'country', 'action' => 'index'))
);
$router->addRoute('cities',
     new Zend_Controller_Router_Route('cities/*', array('controller' => 'city', 'action' => 'index'))
);
$router->addRoute('selected',
     new Zend_Controller_Router_Route('selected/:content_type', array('controller' => 'home', 'action' => 'selected'))
);
$router->addRoute('suggest',
     new Zend_Controller_Router_Route('suggest/:content_type', array('controller' => 'home', 'action' => 'suggest'))
);
$router->addRoute('admin',
     new Zend_Controller_Router_Route('admin/:action/*', array('controller' => 'admin', 'action' => 'index','type' => ''))
);
$router->addRoute('admin_content',
     new Zend_Controller_Router_Route('admin/content/:content_type/:content_action/:params', array('controller' => 'admin', 'action' => 'content','content_type' => '','content_action' => '','params' => ''))
);
?>
