<?php
/**
 * Driver to init the webservice
 */
class WebserviceController extends Zend_Controller_Action {
	
	/**
	 * Webservice handler
	 *
	 * By specifying driver, users are able to choose between SOAP, REST, WSDL driver
	 */
	public function handleAction() {
		$this->getHelper('viewRenderer')->setNoRender();
		
		$serverAction = strtolower($this->_getParam ( 'service' ) );
		$serverController = strtolower($this->_getParam ( 'module' ));
		$serverName = 'Webservice_' . ucfirst($serverController) . '_' . ucfirst($serverAction);
		if (!class_exists($serverName)) {
			throw new Zend_Controller_Action_Exception();
		}
		
		$obj = new $serverName;
		if (!$obj instanceof Webservice_Abstract) {
			throw new Zend_Controller_Action_Exception();
		}
		$obj = null;
		
		try {
			$server = $this->_getServer();
			$server->setClass($serverName);
			$server->handle();
		} catch (Zend_Rest_Server_Exception $e) {
			throw new Zend_Controller_Action_Exception($e);
		}
	}
	
	/**
	 * Return the right server
	 *
	 * @return mixed Correct driver to the server
	 */
	protected function _getServer() {
		$driver = strtolower($this->_getParam('driver'));
		$serverAction = strtolower($this->_getParam ( 'service' ) );
		$serverController = strtolower($this->_getParam ( 'module' ));
		
		switch ($driver) {
			case 'soap':
				$server = new Zend_Soap_Server("http://{$_SERVER['SERVER_NAME']}/{$serverController}/{$serverAction}/driver/wsdl");
				break;
			case 'wsdl':
				$server = new Zend_Soap_AutoDiscover();
				break;
			default:
				$server = new Zend_Rest_Server();
				break;
		}
		return ($server);
	}
}