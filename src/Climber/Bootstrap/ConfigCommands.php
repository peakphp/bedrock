<?php

declare(strict_types=1);

namespace Peak\Climber\Bootstrap;

use Peak\Climber\Application;
use Psr\Container\ContainerInterface;

/**
 * Class ConfigCommands
 * @package Peak\Climber\Bootstrap
 */
class ConfigCommands
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * ConfigCommands constructor.
     *
     * @param Application $app
     * @throws \Peak\Di\Exception\NotFoundException
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        if (!$app->conf()->has('commands') || !is_array($app->conf('commands'))) {
            return;
        }

        $this->add($app->conf('commands'));
    }

    /**
     * Add commands to console application
     *
     * @param array $class
     */
    public function add(array $classes)
    {
        foreach ($classes as $class) {
            $this->app->add(
                $this->app->container()->create($class, [], [
                    ContainerInterface::class => $this->app->container()
                ])
            );
        }
    }
}
