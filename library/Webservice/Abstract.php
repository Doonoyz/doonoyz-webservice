<?php

/**
 * Webservice Abstract class that the service should extend
 */
abstract class Webservice_Abstract {
	/**
	 * Member to have access on the config
	 */
	protected $_config = null;

	/**
	 * Init the service to prepare database connection and configuration
	 */
	final public function __construct() {
		try {
			$config = Zend_Registry::getInstance()->configWebservice;
			$classname = strtolower(get_class($this));
			$configRequire = explode('_', $classname);
			$configname = $configRequire[1];
						
			$specifFile = $config->{$configname}->config->file;
			$specifDomain = ENVIRONMENT;
			
			$this->_config = new Zend_Config_Ini(ROOT_DIR . $specifFile, $specifDomain);
			$registry = Zend_Registry::getInstance();
			$registry->config = $this->_config;
			
			$includePath = $config->{$configname}->includepath;
			set_include_path(get_include_path () . PATH_SEPARATOR . 
							 ROOT_DIR . $includePath);
			
		} catch (Exception $e) {
			$this->_config = null;
			$registry = Zend_Registry::getInstance();
			$registry->config = null;
		}
	}
}