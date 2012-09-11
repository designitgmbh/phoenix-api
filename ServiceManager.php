<?php

	namespace api;
	
    use api\ConfigurationInterface;
	use api\DependencyInjection\ServiceContainerBuilder;

	/**
	 * The service manager helps the dispatcher resolve the dependencies for the system's
	 * components.
	 * 
	 * @author		Daniel Legien <d.legien@design-it.de>
	 * @package		api
	 */
	class ServiceManager implements ServiceManagerInterface
	{
		private
			/**
			 * The services defined in the manager.
			 * 
			 * @var	ServiceContainerBuilder
			 */
			$services,
			
			/**
			 * The collected definitions of the services.
			 * 
			 * @var	array
			 */
			$definitions = array(),
			
			/**
			 * The loaded services.
			 * 
			 * @var	array
			 */			
			$loadedServices = array(),
			
			/**
			 * The configuration passed to the manager.
			 * 
			 * @var	Configuration
			 */
			$configuration,
			
			/**
			 * Whether to cache the service defintions that were gathered
			 * 
			 * @var	bool
			 */
			$cacheOnDeath = FALSE,
			
			/**
			 * The hash of the request, used to determine if a cached entry exists.
			 * 
			 * @var	string
			 */
			$requestHash
		;
		
		/**
		 * Creates a new ServiceManager instance using the provided configuration.
		 * 
		 * @param	Configuration	$configuration	The configuration.
		 */
		public function __construct(ConfigurationInterface $configuration)
		{
			$this->configuration = $configuration;
			$this->services = new ServiceContainerBuilder($this);
			$this->services->setParameters($configuration->getConfiguration());
			
			if($this->services->getParameter('setting.cacheServices'))
			{
				$this->requestHash = $this->services->getService('request')->getHash();
				
				if($this->hasCache($this->requestHash))
				{
					$this->getCache($this->requestHash);
				}
				else
				{
					$this->cacheOnDeath = TRUE;
				}
			}
		}
		
		/**
		 * Returns the service and retrieves it if it is not already loaded.
		 * 
		 * @param	string	$serviceName	The name of the service.
		 * 
		 * @return 	mixed
		 */
		public function getService($serviceName)
		{
			if(!$this->isServiceLoaded($serviceName))
			{
				$this->getDefinition($serviceName);
			}	
			return $this->services->getService($serviceName);
		}
		
		/**
		 * Forwards the request for a parameter to the ServiceContainerBuilder.
		 * 
		 * @param	string	$name	The value of the parameter.
		 * 
		 * @return 	mixed
		 */
		public function getParameter($name)
		{
			return $this->services->getParameter($name);			
		}
		
        /**
         * Forwards the setting of parameters to the ServiceContainerBuilder.
         * 
         * @param   string  $name   The name of the parameter.
         * @param   mixed   $valu   The value of the parameter.
         * 
         * @return  mixed
         */        
		public function setParameter($name, $value)
		{
			$this->services->setParameter($name, $value);
		}
		
		/**
		 * Returns whether the service is loaded or can be loaded.
		 * 
		 * @param	string	$serviceName	The name of the service.
		 * 
		 * @return 	bool
		 */
		public function hasService($serviceName)
		{
			return $this->isServiceLoaded($serviceName) || $this->serviceExists($serviceName);
		}
		
		/**
		 * Returns whether the service is loaded.
		 * 
		 * @param	string	$serviceName	The name of the service.
		 * 
		 * @return 	bool
		 */
		private function isServiceLoaded($serviceName)
		{
			return key_exists($serviceName, $this->loadedServices);	
		}
		
		/**
		 * Flags the service as being loaded to prevent retrieving it
		 * again unnecessarily.
		 * 
		 * @param	string	$serviceName	The name of the service.
		 */
		private function setServiceLoaded($serviceName)
		{
			$this->loadedServices[$serviceName] = TRUE;
		}
		
		/**
		 * Returns whether a definition for the service exists.
		 * 
		 * @param	string	$serviceName	The name of the service.
		 */
		private function serviceExists($serviceName)
		{
 			return is_readable($this->getServicePath($serviceName));
		}			
		
		/**
		 * Returns the path to the service definition file.
		 * 
		 * @param	string	$serviceName	The name of the service.
		 * 
		 * @return 	string
		 */
		private function getServicePath($serviceName)
		{
			return $this->configuration->getSetting('setting.path.services') . str_replace('.', '/', $serviceName) . '.php';			
		}
		
		/**
		 * Retrieves the definition of the service from the corresponding service file.
		 * 
		 * @param	string	$serviceName	The name of the service.
		 * 
		 * @throws	RuntimeException
		 */
		public function getDefinition($serviceName)
		{
			if($this->serviceExists($serviceName))
			{
				$this->definitions[$serviceName] = file_get_contents($this->getServicePath($serviceName));
				include($this->getServicePath($serviceName));
			}
			else
			{
				throw new \RuntimeException("ServiceManager.getDefinition(): Could not locate service $serviceName.");	
			}						
		}
		
		/**
		 * Returns the path to the cache file for the given cache name.
		 * 
		 * @param	string	$cacheName	The name of the cache file.
		 * 
		 * @return 	string
		 */
		private function getServiceCachePath($cacheName)
		{
			return $this->configuration->getSetting('setting.path.servicecache') . "$cacheName.php";
		}
		
		/**
		 * Returns whether a file with the given cachename exists in the cahce
		 * directory.
		 * 
		 * @param	string	$cacheName	The name of the cache file.
		 * 
		 * @return 	bool
		 */
		private function hasCache($cacheName)
		{
			return file_exists($this->getServiceCachePath($cacheName));
		}
		
		/**
		 * Include the cache with the given name to retrieve all definitions
		 * at once.
		 * 
		 * @param	string	$cacheName	The name of the cache file
		 */
		private function getCache($cacheName)
		{
			if($this->hasCache($cacheName))
			{
				include($this->getServiceCachePath($cacheName));
			}
		}
		
		/**
		 * Minifies the given string of data by removing unneccessary php tags,
		 * whitespaces and comments
		 * 
		 * @param	string	$data	The string to minify.
		 * 
		 * @return 	string
		 */
		private function minify($data)
		{
			$data = preg_replace(array('^[/]?[*]+[\s\n\t\w*.\-\<\>@]*[*]?[/]?^', '^// (.*)^','^\s^'), '', $data);
			return str_replace(array('<?php', '?>'), '', $data);
		}
		
		/**
		 * Sets the cache file content to the current definitions and
		 * minifies them.
		 * 
		 * @param	string	$cacheName	The name of the cachefile.
		 */
		private function setCache($cacheName)
		{
			file_put_contents($this->getServiceCachePath($cacheName), "<?php\n" . $this->minify(implode('',$this->definitions)));
		}
		
		/**
		 * Destructor called when the object is destroyed. If the definitions need
		 * to be cached then we call the appropiate method.
		 */
		public function __destruct()
		{
			if($this->cacheOnDeath)
			{
				$this->setCache($this->requestHash);
			}
		}
		
		/**
		 * Forwards the registration to the ServiceContainerBuilder and also
		 * registers the loading of the service to prevent additional retrieval
		 * of the service.
		 * 
		 * @param	string	$serviceName	The name of the service
		 * @param	string	$class 			The name of the class
		 * 
		 * @return 	ServiceDefinition
		 */
		public function register($serviceName, $class)
		{
			$this->setServiceLoaded($serviceName);
			return $this->services->register($serviceName, $class);
		}
	}	
	