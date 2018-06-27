<?php

namespace Peak\Bedrock\Application;

use Peak\Bedrock\Application\Config\AppTree;
use Peak\Bedrock\Application\Exceptions\MissingConfigException;
use Peak\Common\DataException;
use Peak\Config\ConfigLoader;

class ConfigResolver
{
    /**
     * Default config
     * @var array
     */
    private $default = [
        'ns' => 'App',          //namespace
        'env' => 'prod',        //app environment (dev,prod,staging,testing)
        'conf' => [],           //config(s) file(s)
        'name' => 'app',        //default application name
        'path' => [             //paths
            'public'  => '',
            'app'     => '',
            'apptree' =>  []
        ],
    ];

    /**
     * The final app config collection
     * @var object
     */
    protected $app_config;

    /**
     * ConfigResolver constructor.
     *
     * @param array $config
     * @throws DataException
     * @throws MissingConfigException
     * @throws \Peak\Config\Exception\UnknownTypeException
     */
    public function __construct($config = [])
    {
        // validate user conf
        $this->validate($config);

        // define default app constants
        $this->defineConstants($config);

        // get application path tree
        $config['path']['apptree'] = (new AppTree(APPLICATION_ABSPATH))->get();

        // prepare the final app configuration
        $final = [
            $this->default,
            $config
        ];

        // load external app config
        if (isset($config['conf'])) {
            if (is_string($config['conf'])) {
                $final[] = $config['conf'];
            } elseif (is_array($config['conf'])) {
                foreach ($config['conf'] as $conf) {
                    $final[] = $conf;
                }
            }
        }

        // build and store final application config
        $this->app_config = new Config(
            (new ConfigLoader($final))->asArray()
        );
    }

    /**
     * Get app configuration
     *
     * @return Config
     */
    public function getMountedConfig()
    {
        return $this->app_config;
    }

    /**
     * Validate require config values
     *
     * @param array $config
     * @throws MissingConfigException
     * @throws DataException
     */
    private function validate($config)
    {
        if (!isset($config['env'])) {
            throw new MissingConfigException('env');
        }

        if (!isset($config['path']['public'])) {
            throw new MissingConfigException('path.public');
        }

        if (!file_exists($config['path']['public'])) {
            throw new DataException('Public path not found', $config['path']['public']);
        }

        if (!isset($config['path']['app'])) {
            throw new MissingConfigException('path.app');
        }
        if (!file_exists($config['path']['app'])) {
            throw new DataException('Application path not found', $config['path']['app']);
        }
    }

    /**
     * Define important constants
     *
     * @param array $config
     */
    private function defineConstants($config)
    {
        //define server document root absolute path
        $doc_path = str_replace('\\', '/', realpath(filter_var(getenv('DOCUMENT_ROOT'))));
        if (substr($doc_path, -1, 1) !== '/') {
            $doc_path .= '/';
        }

        define('ROOT_ABSPATH', $doc_path);
        define('PUBLIC_ABSPATH', realpath($config['path']['public']));
        define('APPLICATION_ABSPATH', realpath($config['path']['app']));
        define('APPLICATION_ENV', $config['env']);
    }
}
