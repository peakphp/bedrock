<?php

declare(strict_types=1);

namespace Peak\Di;

use Peak\Di\Exception\NotFoundException;
use \Exception;

/**
 * Class InterfaceResolver
 * @package Peak\Di
 */
class InterfaceResolver
{
    /**
     * Resolve class arguments dependencies
     *
     * @param $interface
     * @param Container $container
     * @param array $explicit
     * @return null|object
     * @throws NotFoundException
     * @throws Exception
     */
    public function resolve($interface, Container $container, $explicit = [])
    {
        // Try to find a match in the container for a class or an interface
        if ($container->hasInterface($interface)) {
            $instance = $container->getInterface($interface);
            if (is_array($instance)) {
                if (empty($explicit) || !array_key_exists($interface, $explicit)) {
                    throw new Exception('Dependecies for interface '.$interface.' is ambiguous. There is '.count($instance).' differents stored instances for this interface.');
                }
                return $container->get($explicit[$interface]);
            }
            return $container->get($instance);
        }
        throw new Exception('Could not find an instance that implement interface '.$interface);
    }
}
