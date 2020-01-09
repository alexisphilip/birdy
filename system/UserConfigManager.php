<?php

/**
 * Class UserConfigManager.
 *
 * This static class is used to set the user configuration.
 * The user configuration can be accessed with the static function getConfiguration.
 */
class UserConfigManager
{
    static $user_config;

    /**
     * Sets the user configuration.
     */
    public static function initConfig(): void
    {
        // Auto-load all of the user's configuration files.
        $path = "./app/config/*.php";
        foreach (glob($path) as $filename) {
            require_once $filename;
        }

        // TODO: assign user config vars automatically.
        self::$user_config["autoload"] = $autoload;
        self::$user_config["database"] = $database;
        self::$user_config["global"] = $global;
        self::$user_config["multi_language"] = $multi_language;
        self::$user_config["groups"] = $groups;
        self::$user_config["route"] = $route;
        self::$user_config["title"] = $title;
    }

    /**
     * Sets a user configuration element.
     *
     * @param string $category The configuration category: title, autoload...
     * @param string $element The category's element: (title) default, default_prefix...
     * @param mixed $value The value to set.
     */
    public static function setConfigElement(string $category, string $element, $value): void
    {
        self::$user_config[$category][$element] = $value;
    }

    /**
     * Gets the whole user configuration.
     *
     * @return mixed
     */
    public static function getConfig(): array
    {
        return self::$user_config;
    }
}
