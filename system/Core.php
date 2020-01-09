<?php


/**
 * Class Core.
 */
class Core
{
    /**
     * Core constructor.
     *
     * Sets the user configuration and start the router.
     */
    public function start()
    {
        // Sets the user configuration.
        UserConfigManager::initConfig();

        // Adds the files to be included the include list from user config.
        UserConfigListManager::addToIncludeListFromConfig();

        // Ready to route. Starts the router.
        $router = new Router;
        $router->route();
    }
}
