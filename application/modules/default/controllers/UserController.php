<?php

class UserController extends App_Controller_Action
{
    public function loginAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $redirect = $request->getParam('redirect','');

            $username = trim($request->getParam('username',''));
            $password = trim($request->getParam('password',''));

            if ($username == '' || $password == '') {
                $this->_helper->flashMessenger->addMessage(array('type' => 'error','text' => $this->view->i18n->t('error.empty_fields')));
    				return ($redirect != '') ? $this->_redirect($redirect) : $this->_helper->redirector->gotoRoute(array(), 'default', true, false);
            }

            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

            $authAdapter->setTableName('UserAdmin')
                        ->setIdentityColumn('username')
                        ->setCredentialColumn('password')
                        ->setCredentialTreatment('MD5(?)');

            $authAdapter->setIdentity($username)
                        ->setCredential($password);

            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($authAdapter);

            if ($result->isValid()) {
                $userInfo = $authAdapter->getResultRowObject(null, 'password');
                $authStorage = $auth->getStorage();
                $authStorage->write($userInfo);
                
              	if ((int)$request->getParam('remember_me') == 1) {
              		Zend_Session::rememberMe((24 * 3600));
	    			}
            }
            else {
                $this->_helper->flashMessenger->addMessage(array('type' => 'error','text' => $this->view->i18n->t('error.wrong_auth_info')));
            }

            return ($redirect != '') ? $this->_redirect($redirect) : $this->_helper->redirector->gotoRoute(array(), 'default', true, false);
        }
    }

    public function logoutAction()
    {
        Zend_Session::forgetMe();
    		Zend_Auth::getInstance()->clearIdentity();
        return $this->_helper->redirector->gotoRoute(array(), 'admin', true, false);
    }
}

?>