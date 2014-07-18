<?php

namespace Broda\Framework\Api;

use Broda\Framework\Application;

/**
 * Interface that must implement all Silex service providers.
 *
 * Based on Silex's Api by Fabien Potencier <fabien@symfony.com>
 *
 * @author raphael
 */
interface BootableProviderInterface
{
    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app);
}
