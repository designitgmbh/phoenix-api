<?php

	namespace api;

    use api\ConfigurationInterface;

	/**
	 * Represents the configuration of a system.
	 * 
	 * @author		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 * 
	 */
	class Configuration implements ConfigurationInterface
	{
		private
			/**
			 * The configuration settings.
			 * 
			 * @var	array
			 */
			$settings
		;
		
		/**
		 * Creates a new Configuration out of the given filename.
		 * 
		 * @param	string	$fileName	The name of the configuration file.
		 * 
		 * @throws	RuntimeException
		 */
		public function __construct($param) {
			if (is_string($param) === true) {
				$this->setConfiguration(
					$this->getSettingsFromFile(
						$param
					)
				);
			}
							
			if (is_array($param) === true) {
				$this->setConfiguration($param);
			}
		}
		
		/**
		 * 
		 */
		private function setConfiguration($settings) {
			$this->settings = (array) $settings;
		}
		
		/**
		 * Retrieves the settings from the configuration file or throws a RuntimeException
		 * if no configuration file was found. After including the configuration it checks
		 * the existance of settings and throws a RuntimeException if no settings are found.
		 * 
		 * @throws	RuntimeException
		 */
		private function getSettingsFromFile($filename) {
			if(is_readable($filename) === false) {	
				throw new \RuntimeException(__METHOD__ . ': Configuration file ' . $filename . ' is not readable.');	
			}
			
			include($filename);
			
			if(isset($settings) === false) {
				throw new \RuntimeException(__METHOD__ . ': Invalid configuration file. Array $settings could not be found.');
			}
			
			return $settings;
		}
		
		/**
		 * Returns the complete configuration.
		 * 
		 * @return 	array
		 */
		public function getConfiguration()
		{
			return $this->settings;
		}
		
		/**
		 * Returns whether a setting with the given name exists.
		 * 
		 * @param	string	$settingName	The name of the setting.
		 * 
		 * @return 	bool
		 */
		private function hasSetting($settingName)
		{
			return key_exists($settingName, $this->settings);
		}
		
		/**
		 * Returns the setting with the given name or throws an InvalidArgumentException
		 * if a setting with that name does not exist.
		 * 
		 * @param	string	$settingName	The name of the setting
		 * 
		 * @return 	mixed
		 * @throws	InvalidArgumentException
		 */
		public function getSetting($settingName)
		{
			if(!$this->hasSetting($settingName))
			{
				throw new \InvalidArgumentException("Configuration.getSetting(): Retrieving non-existant setting $settingName");	
			}	
			return $this->settings[$settingName];
		}
	}
		