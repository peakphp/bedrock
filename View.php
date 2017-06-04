<?php

namespace Peak\Bedrock;

use Peak\Bedrock\Application;
use Peak\Bedrock\View\Header;
use \Exception;

class View
{
    /**
     * view vars
     * @var array
     */
    protected $_vars = [];

    /**
     * view helpers objects
     * @var array
     */
    private $_helpers = [];

    /**
     * view header object
     * @var object
     */
    private $_header;

    /**
     * view rendering object
     * @var object
     */
    private $_engine;
    
    /**
     * Determine if view will be rendered(view engine executed)
     * @var bool
     */
    private $_render = true;

    /**
     * Constructor
     */
    public function __construct(array $vars = [])
    {   
        $this->_vars = $vars;
    }

    /**
     * Set/overwrite view variable
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value = null)
    {
        $this->_vars[$name] = $value;
    }

    /**
     * Get view variable
     *
     * @param  string $name
     * @return mixed
     */
    public function &__get($name)
    {
        if (isset($this->_vars[$name])) {
            return $this->_vars[$name];
        }
        return ${null};
    }

    /**
     * Isset $vars keyname
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_vars) ? true : false;
    }

    /**
     * Unset $vars keyname
     *
     * @param string $name
     */
    public function __unset($name)
    {
        if (array_key_exists($name, $this->_vars)) {
            unset($this->_vars[$name]);
        }
    }

    /**
     * We try to call View Render Engine object method.
     * If not, we try to return a helper object based on $method name.
     * So every Rendering Engine Method can be called directly inside Peak\Bedrock\View and
     * every instantiated Peak_View_Helpers
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args = null)
    {
        if (method_exists($this->engine(), $method)) {
            return call_user_func_array([$this->engine(), $method], $args);
        } else {
            return $this->helper($method, $args);
        }
    }

    /**
     * Set/overwrite view variable
     *
     * @see    __set()
     * @return $this
     */
    public function set($name, $value = null)
    {
        $this->__set($name, $value);
        return $this;
    }

    /**
     * Count template variables
     *
     * @return integer
     */
    public function countVars()
    {
        return count($this->getVars());
    }

    /**
     * Get template variables
     *
     * @return array
     */
    public function getVars()
    {
        return $this->_vars;
    }

    /**
     * Set/Overwrite some view vars
     *
     * @param array $vars
     */
    public function setVars($vars)
    {
        $this->_vars = $vars;
    }

    /**
     * Set/Add some view vars
     * Existing var key name will be overwritten, otherwise var is added to current $_vars
     */
    public function addVars($vars)
    {
        foreach ($vars as $k => $v) {
            $this->set($k, $v);
        }
    }

    /**
     * Clean all variable in $vars
     */
    public function resetVars()
    {
        $this->_vars = [];
    }

    /**
     * Set/Get current view rendering engine object
     *
     * @param  string $engine_name
     * @return object Peak_View_Render_*
     */
    public function engine($engine_name = null)
    {
        if (isset($engine_name)) {
            $engine_name = strip_tags(ucfirst($engine_name));
            $engine_class = 'Peak\Bedrock\View\Render\\'.$engine_name;
            if (!class_exists($engine_class)) {
                throw new Exception('View rendering engine '.$engine_name.' not found');
            }
            $this->_engine = Application::instantiate($engine_class);
        }

        return $this->_engine;
    }

    /**
     * Get render engine name
     *
     * @return string
     */
    public function getEngineName()
    {
        if (is_object($this->_engine)) {
            return strtolower(str_replace('Peak\Bedrock\View\Render\\', '', get_class($this->_engine)));
        }
        return null;
    }
    
    /**
     * Return render option
     *
     * @return bool
     */
    public function canRender()
    {
        return $this->_render;
    }

    /**
     * Disable rendering
     *
     * @return $this
     */
    public function disableRender()
    {
        $this->_render = false;
        return $this;
    }
    
    /**
     * Enabled rendering
     *
     * @return $this
     */
    public function enableRender()
    {
        $this->_render = true;
        return $this;
    }

    /**
     * Render Controller Action View file with the current rendering engine
     *
     * @param  string $file
     * @param  string $path
     * @return string or array   return array of view files when layout is used
     */
    public function render($file, $path = null)
    {
        //skip render part(see $_render)
        if ($this->_render === false) {
            return;
        }

        if (is_object($this->_engine)) {
            // check if we got http header
            if (is_object($this->_header)) {
                $this->_header->release();
            }

            $this->engine()->render($file, $path);
        } else {
            throw new Exception('View rendering engine not set');
        }
    }

    /**
     * Load/get HTTP header object
     *
     * @return object \Peak\Bedrock\View\Header
     */
    public function header()
    {
        if (!is_object($this->_header)) {
            $this->_header = new Header();
        }

        return $this->_header;
    }

    /**
     * Load helpers objects method and return helper obj
     *
     * @return object Peak\Bedrock\View\Helpers
     */
    public function helper($name = null, $params = [])
    {
        if (array_key_exists($name, $this->_helpers)) {
            return $this->_helpers[$name];
        } else {
            $peak_helper = 'Peak\Bedrock\View\Helper\\'.ucfirst($name);
            $app_helper  = Application::conf('ns').'\Views\Helpers\\'.ucfirst($name);

            if (class_exists($peak_helper)) {
                $this->_helpers[$name] = Application::instantiate($peak_helper, $params);
                return $this->_helpers[$name];
            } elseif (class_exists($app_helper)) {
                $this->_helpers[$name] = Application::instantiate($app_helper, $params);
                return $this->_helpers[$name];
            } else {
                trigger_error('[ERR] View helper '.$name.' doesn\'t exists');
            }
        }
    }
}
