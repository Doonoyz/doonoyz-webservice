<?php
/**
 * Error Controller
 *
 */
class ErrorController extends Zend_Controller_Action {

	/**
	 * Protect controller against direct access
	 *
	 */
	public function indexAction() {
		$this->_redirect ( '/' );
	}

	/**
	 * Catch all the errors and do the right action
	 *
	 */
	public function errorAction() {
		$errors = $this->_getParam ( 'error_handler' );
		$content = 'error';

		switch ($errors->type) {
			/**
			 * If is a known error
			 */
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :
				$this->getResponse ()->setRawHeader ( 'HTTP/1.1 200 OK' );
				$options = array ('module' => $this->_getParam ( 'controller' ), 'service' => $this->_getParam ( 'action' ));
				$this->_forward ( 'handle', 'webservice' , 'default', $options);
				return;
				break;
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION :
				// 404 error -- controller or action not found
				$this->view->setLayout ( 'default' );
				$this->view->addLayoutVar ( 'title', t_( "Error" ) );

				$this->getResponse ()->setRawHeader ( 'HTTP/1.1 404 Not Found' );

				$content = t_( "The page you requested was not found." );
				break;
			/**
			 * Any other error
			 */
			default :
				// application error
				$this->getResponse ()->setRawHeader ( 'HTTP/1.1 500 Internal Server Error' );
				$content = t_( "An unexpected error occurred with your request. Please try again later." );
				break;
		}
        $this->getHelper('viewRenderer')->setNoRender();
		// Clear previous content
		$this->getResponse ()->setHeader('Content-Type', 'text/html');
		$this->getResponse ()->setBody ($content);
	}
}
