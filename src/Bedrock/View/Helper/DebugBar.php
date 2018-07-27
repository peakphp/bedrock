<?php

declare(strict_types=1);

namespace Peak\Bedrock\View\Helper;

use Peak\DebugBar\DebugBar as DebugBarComponent;

/**
 * Class DebugBar
 * @package Peak\Bedrock\View\Helper
 */
class DebugBar
{
    /**
     * @var DebugBarComponent
     */
    protected $dbar;

    /**
     * @var array
     */
    protected $modules = [
        \Peak\Bedrock\View\Helper\DebugBar\Modules\Version\Version::class,
        \Peak\DebugBar\Modules\ExecutionTime\ExecutionTime::class,
        \Peak\DebugBar\Modules\Memory\Memory::class,
        \Peak\DebugBar\Modules\Message\Message::class,
        \Peak\DebugBar\Modules\Files\Files::class,
        \Peak\Bedrock\View\Helper\DebugBar\Modules\ViewVars\ViewVars::class,
        \Peak\DebugBar\Modules\Session\Session::class,
        \Peak\DebugBar\Modules\Inputs\Inputs::class,
        \Peak\DebugBar\Modules\Headers\Headers::class,
        \Peak\Bedrock\View\Helper\DebugBar\Modules\AppContainer\AppContainer::class,
        \Peak\Bedrock\View\Helper\DebugBar\Modules\AppConfig\AppConfig::class,
        \Peak\DebugBar\Modules\UserConstants\UserConstants::class,
    ];

    /**
     * DebugBar constructor.
     */
    public function __construct()
    {
        $this->dbar = new DebugBarComponent(null, $this->modules);
    }

    /**
     * Return DebugBar instance
     *
     * @return DebugBarComponent
     */
    public function bar()
    {
        return $this->dbar;
    }

    /**
     * Render and output the bar
     *
     * @throws \Peak\DebugBar\Exceptions\InvalidModuleException
     * @throws \Peak\DebugBar\Exceptions\ModuleNotFoundException
     * @throws \Peak\DebugBar\View\ViewNotFoundException
     */
    public function render()
    {
        echo $this->dbar->render();
    }
}
