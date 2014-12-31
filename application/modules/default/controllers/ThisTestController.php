<?php

require_once "Zend/Controller/Action.php";

class Default_ThisTestController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    public function testingAction($id){
		    $this->_helper->layout->disableLayout();
		    $this->_helper->viewRenderer->setNoRender(TRUE);
		    //$id = $this->getEvent()->getRouteMatch()->getParam('id');
		    $params = $this->_getAllParams();
		    echo $params['id'];
		    echo "here";
    	
    }


}
?>