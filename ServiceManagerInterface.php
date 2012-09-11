<?php

    namespace api;
    
    use api\ConfiguartionInterface;
    
    /**
     * The ServiceManagerInterface specifies the methods necessary to implement
     * a ServiceManager.
     * 
     * The ServiceManager is used to resolve dependencies between components of
     * the application by using service definitions.
     * 
     * @author      Daniel Legien <d.legien@design-it.de>
     * @package     api
     * 
     */
    interface ServiceManagerInterface
    {
      
        /**
         * Creates a new ServiceManager instance using the provided configuration.
         * 
         * @param   Configuration   $configuration  The configuration.
         */
        public function __construct(ConfigurationInterface $configuration);
        
        /**
         * Returns the service and retrieves it if it is not already loaded.
         * 
         * @param   string  $serviceName    The name of the service.
         * 
         * @return  mixed
         */
        public function getService($serviceName);
        
        /**
         * Forwards the request for a parameter to the ServiceContainerBuilder.
         * 
         * @param   string  $name   The name of the parameter.
         * 
         * @return  mixed
         */
        public function getParameter($name);
        
        /**
         * Forwards the setting of parameters to the ServiceContainerBuilder.
         * 
         * @param   string  $name   The name of the parameter.
         * @param   mixed   $valu   The value of the parameter.
         * 
         * @return  mixed
         */
        public function setParameter($name, $value);
        
        /**
         * Returns whether the service is loaded or can be loaded.
         * 
         * @param   string  $serviceName    The name of the service.
         * 
         * @return  bool
         */
        public function hasService($serviceName);
               
        /**
         * Retrieves the definition of the service from the corresponding service file.
         * 
         * @param   string  $serviceName    The name of the service.
         * 
         * @throws  RuntimeException
         */
        public function getDefinition($serviceName);
                        
        /**
         * Forwards the registration to the ServiceContainerBuilder and also
         * registers the loading of the service to prevent additional retrieval
         * of the service.
         * 
         * @param   string  $serviceName    The name of the service
         * @param   string  $class          The name of the class
         * 
         * @return  ServiceDefinition
         */
        public function register($serviceName, $class);
    }   

