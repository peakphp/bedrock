<?php

namespace Peak\Bedrock\Application;

use Peak\Exception;
use Peak\Bedrock\Application\Config;
use Peak\Bedrock\Application\Config\FileLoader;
use Peak\Bedrock\Application\Config\Environment;

class ConfigResolver
{
    /**
     * Default config
     * @var array
     */
    private $default = [
        'ns'   => 'App',  //namespace
        'env'  => 'prod',
        'conf' => 'config.php',
        'path' => [
            'public' => '',
            'app'    => '',
            'apptree' =>  []
        ],
    ];

    /**
     * The final app config collection
     * @var object
     */
    protected $app_config;

    /**
     * Construct
     *
     * @param array $config user config
     */
    public function __construct($config = [])
    {
        $this->app_config = new Config($this->default);
        $this->app_config->mergeRecursiveDistinct($config);

        $this->validate(); // validate user conf
        $this->defineConstants(); // define default app constants

        //print_r($this->app_config);

        $config_loader = new FileLoader($this->getConfigFilepath());

        $config_env = new Environment(
            $config_loader->getConfig(),
            $this->app_config
        );

        $this->app_config->mergeRecursiveDistinct($config_env->getEnvConfig());
    }

    /**
     * Get app config
     *
     * @return object
     */
    public function getMountedConfig()
    {
        return $this->app_config;
    }

    /**
     * Get application config filepath
     *
     * @return string
     */
    private function getConfigFilepath()
    {
        $path = str_replace('\\', '/',
            realpath($this->app_config->get('path.app').'/'.$this->app_config->get('conf'))
        );

        return $path;
    }

    /**
     * Define important constants
     */
    private function defineConstants()
    {
        //define server document root absolute path
        $svr_path = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
        if (substr($svr_path, -1, 1) !== '/') {
            $svr_path .= '/';
        }

        define('SVR_ABSPATH',         $svr_path);
        define('LIBRARY_ABSPATH',     realpath(__DIR__.'/../'));
        define('PUBLIC_ABSPATH',      realpath($this->app_config->get('path.public')));
        define('APPLICATION_ABSPATH', realpath($this->app_config->get('path.app')));
        define('APPLICATION_ENV',     $this->app_config->get('env'));
    }

    /**
     * Validate require config values
     */
    private function validate()
    {
        if (!$this->app_config->have('path.public')) {
            throw new Exception('ERR_CORE_INIT_CONST_MISSING', ['Public root','PUBLIC_ROOT']);
        }

        if (!$this->app_config->have('path.app')) {
            throw new Exception('ERR_CORE_INIT_CONST_MISSING', ['Application root','APPLICATION_ROOT']);
        }

        if (!$this->app_config->have('env')) {
            throw new Exception('ERR_APP_ENV_MISSING');
        }

        if (!$this->app_config->have('conf')) {
            throw new Exception('ERR_APP_CONF_MISSING');
        }
    }
}
