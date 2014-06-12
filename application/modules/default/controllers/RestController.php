<?php

class RestController extends Zend_Rest_Controller
{
	
    protected $controller_name = 'rest';

    public function init()
    {
        $this->_helper->Layout->disableLayout();
        $this->_helper->ViewRenderer->setNoRender();
    }

    public function indexAction()
    {

    }

    public function getAction()
    {
        $api_response_format = Zend_Registry::get('config')->api_response_format;

        if (isset($this->view->data) && count($this->view->data) > 0) {
            header("Content-type: application/".$api_response_format);
            switch ($api_response_format) {
                case 'json':
                    echo Zend_Json::encode($this->view->data);
                    break;
                default:
                    $xml = new XmlWriter();
                    $xml->openMemory();
                    $xml->startDocument('1.0', 'UTF-8');
                    $xml->startElement('content');

                    foreach ($this->view->data as $data) {
                        $xml->startElement($this->_getParam('content_type'));
                        $this->_writeXML($xml,$data);
                        $xml->endElement();
                    }

                    $xml->endElement();
                    echo $xml->outputMemory(true);
                    break;
            }
        }
        
        die();
    }

    public function postAction()
    {
        
    }

    public function putAction()
    {
        
    }

    public function deleteAction() {

    }

    protected function _writeXML(XMLWriter $xml, $data)
    {
        foreach($data as $key => $value)
        {
            if(is_array($value)){
                $xml->startElement($key);
                $this->_writeXML($xml, $value);
                $xml->endElement();
                continue;
            }
            $xml->writeElement($key, $value);
        }
    }

}

?>