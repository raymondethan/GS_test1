<?php

/**
 * App_Controller_Plugin_FlashMessenger
 *
 * Plugin, check if there is an message to render
 *
 * @author TulikaEvheniy
 */
class App_Controller_Plugin_FlashMessenger extends Zend_Controller_Plugin_Abstract
{

   /**
    * Catch postDispatch event
    *
    */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $actionHelperFlashMessenger = new Zend_Controller_Action_Helper_FlashMessenger();
        $messagesSuccess = $actionHelperFlashMessenger->setNamespace('messages')->getMessages();
        if (empty($messagesSuccess) || !$request->isDispatched()) {
        	return;
        }
        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();
        $view->messages = $messagesSuccess;

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer
            ->setView($view)
            ->renderScript('messages.tpl', 'messages')
            ;
    }
}
