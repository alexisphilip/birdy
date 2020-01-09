<?php

/**
 * Class UserConfigListManager.
 *
 * Manages the adding and formatting of the files to be included ("css", "js", "helper" and "translation").
 * It includes automatically the files set in the "autoload" user configuration file,
 * and the ones set in the controller with "loadCSS()", "loadJS()", "loadHelpers()" and "loadTranslations()" functions.
 */
class UserConfigListManager extends Core
{
	static $groups = [];
    static $name_css = [];
    static $name_js = [];
    static $name_helper = [];
    static $name_translation = [];

    /**
     * Adds the files to be included the include list from user config.
     */
    public static function addToIncludeListFromConfig(): void
    {
        // All the autoload config.
        $autoload = UserConfigManager::getConfig()["autoload"];

        // All the files groups to load.
        UserConfigListManager::$groups = UserConfigManager::getConfig()["groups"];

        // Autoloads the files from the autoload config.
		UserConfigListManager::addToIncludeListFromName("css",     $autoload["css"]);
		UserConfigListManager::addToIncludeListFromName("js",      $autoload["js"]);
		UserConfigListManager::addToIncludeListFromName("helpers", $autoload["helpers"]);
		UserConfigListManager::addToIncludeListFromName("translations", $autoload["translations"]);
    }

    /**
	 * This method loops through and adds to an  list all elements which need to be "loaded".
	 *
	 * If an argument is a string, it is a file name. It adds its name the including list.
	 * If an argument is an array with the length of 1, it looks in the correct group list if
	 * the name matches a list of elements. If it does, it adds its elements to the  list.
	 * 
     * @param string $type "css", "js", "helper" or "translation".
     * @param array $args The associative array containing the file / group names to include.
     */
    public static function addToIncludeListFromName(string $type, array $args): void
    {
        foreach ($args as $key => $value) {
            // If an array is passed: is it a group name.
            if (gettype($value) == "array") {
                $group_name = $value[0]; // Is the name of the group of elements to load.

                // For each elements in the group.
                foreach (UserConfigListManager::$groups[$type][$group_name] as $key => $name) {
                    UserConfigListManager::pushName($type, $name);
                }
            }
            // If a string is passed: it is an element name.
            else if (gettype($value) == "string") {
                $name = $value; // Is the name of the element to load.
                UserConfigListManager::pushName($type, $name);
            }
        }
    }

    /**
     * Pushes a specific element name in its array if it is not already in it.
     *
     * @param string $type "css", "js", "helper" or "translation".
     * @param string $name The name of the file to add the include array.
     */
    private static function pushName(string $type, string $name): void
    {
        // TODO: Instead of making conditions, make dynamic variable.
        if ($type == "css" && !in_array($name, UserConfigListManager::$name_css, true)) {
            UserConfigListManager::$name_css[] = $name;
        }
        else if ($type == "js" && !in_array($name, UserConfigListManager::$name_js, true)) {
            UserConfigListManager::$name_js[] = $name;
        }
        else if ($type == "helpers" && !in_array($name, UserConfigListManager::$name_helper, true)) {
            UserConfigListManager::$name_helper[] = $name;
        }
        else if ($type == "translations" && !in_array($name, UserConfigListManager::$name_translation, true)) {
            UserConfigListManager::$name_translation[] = $name;
        }
    }

    /**
     * Gets the CSS name list.
     *
     * @return array
     */
    public static function getNameCss(): array
    {
        return UserConfigListManager::$name_css;
    }

    /**
     * Gets the JS name list.
     *
     * @return array
     */
    public static function getNameJs(): array
    {
        return UserConfigListManager::$name_js;
    }

    /**
     * Gets the helpers name's list.
     *
     * @return array
     */
    public static function getNameHelpers(): array
    {
        return UserConfigListManager::$name_helper;
    }

    /**
     * Gets the translations name's list.
     *
     * @return array
     */
    public static function getNameTranslations(): array
    {
        return UserConfigListManager::$name_translation;
    }
}