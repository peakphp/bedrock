<?php

namespace Peak\Bedrock\Controller;

use Peak\Bedrock\Application;
use Peak\Bedrock\Application\Config;
use Peak\Routing\Route;
use Peak\Bedrock\View;
use \Exception;

/**
 * Action(s) based controller
 */
abstract class ActionController
{
    /**
     * view script file to render
     * @var string
     */
    public $file;

    /**
     * action called by dispatchAction()
     * @var string
     */
    public $action;
    
    /**
     * instance of view
     * @var View
     */
    public $view;

    /**
     * instance of application config
     * @var Config
     */
    public $config;

    /**
     * Params collection
     * @var ParamsCollection
     */
    public $params;
    
    /**
     * Action method prefix
     * @var string
     */
    protected $action_prefix = '_';

    /**
     * Constructor
     *
     * @param View $view
     */
    public function __construct(View $view, Config $config)
    {
        $this->view = $view;
        $this->config = $config;
    }

    /**
     * Get controller class title
     *
     * @return string
     */
    public function getTitle()
    {
        return str_ireplace('controller', '', shortClassName($this));
    }

    /**
     * Get action title
     *
     * @return string
     */
    public function getActionTitle()
    {
        return substr($this->action, strlen($this->action_prefix));
    }

    /**
     * Get data from router needed for dispatch
     */
    public function setRoute(Route $route)
    {
        $this->params = new ParamsCollection($route->params);
        $this->action = $this->action_prefix . $route->action;

        //set default ctrl action if none present
        if ($this->action === $this->action_prefix) {
            $this->action = $this->action_prefix.'index';
        }
    }

    /**
     * Dispatch controller action and other stuff around it
     *
     * @throws Exception
     */
    public function dispatch()
    {
        $this->preAction();
        $this->dispatchAction();
        $this->postAction();
    }

    /**
     * Dispatch action requested by router or the default action(_index)
     *
     * @throws Exception
     */
    public function dispatchAction()
    {
        if ($this->isAction($this->action) === false) {
            throw new Exception('Controller action '.$this->action.' not found in '.shortClassName($this));
        }

        $this->file = strtolower($this->getTitle().'.'.$this->getActionTitle().'.php');

        $this->callAction($this->action);
    }

    /**
     * Call an action
     *
     * @param   string $action
     * @param   array  $args
     * @return  mixed
     */
    protected function callAction($action, $args = [])
    {
        return call_user_func_array([$this, $action], $args);
    }

    /**
     * Check if action method name exists
     *
     * @param  string $name
     * @return bool
     */
    public function isAction($name)
    {
        return (method_exists($this, $name)) ? true : false;
    }

    /**
     * Call view render with controller $file and $path
     *
     * @return string
     * @throws Exception
     */
    public function render()
    {
        $this->view->render(
            $this->file,
            $this->config->get('path.apptree.views_scripts')
        );
        $this->postRender();
    }

    /**
     * Call front controller redirect() method
     *
     * @param string     $ctrl
     * @param string     $action 'index' by default
     * @param array|null $params
     */
    public function redirect($ctrl, $action = 'index', $params = null)
    {
        Application::kernel()->front->redirect($ctrl, $action, $params);
    }

    /**
     * Call front controller redirect() method.
     * Same as redirect() but redirect to an action in the current controller only
     *
     * @param string     $action
     * @param array|null $params
     */
    public function redirectAction($action, $params = null)
    {
        $this->redirect($this->getTitle(), $action, $params);
    }

    /**
     * Use View helper "redirect" to make a HTTP header redirection
     *
     * @param string  $url
     * @param bool    $base_url
     * @param integer $http_code
     */
    public function redirectUrl($url, $http_code = 302, $base_url = true)
    {
        if ($base_url) {
            $url = url($url);
        }
        $this->view->header()->redirect($url, $http_code);
    }

    /**
     * Action before controller requested action
     */
    public function preAction()
    {
    }

    /**
     * Action after controller requested action
     */
    public function postAction()
    {
    }

    /**
     * Action after view rendering
     */
    public function postRender()
    {
    }
}
