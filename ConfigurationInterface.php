<?php

    namespace api;
        
    /**
     * The ConfigurationInterface specifies the methods required to implement a
     * Configuration. The Configuration class represents the configuration of a
     * system.
     * 
     * @author      Daniel Legien <d.legien@design-it.de>
     * @package     api
     * 
     */
    interface ConfigurationInterface
    {               
        /**
         * Returns the complete configuration.
         * 
         * @return  array
         */
        public function getConfiguration();
                
        /**
         * Returns the setting with the given name or throws an InvalidArgumentException
         * if a setting with that name does not exist.
         * 
         * @param   string  $settingName    The name of the setting
         * 
         * @return  mixed
         * @throws  InvalidArgumentException
         */
        public function getSetting($settingName);   
    }
