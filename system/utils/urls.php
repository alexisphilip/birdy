<?php

/**
 * Returns the server domain name.
 *
 * @return string
 */
function base_url(): string
{
    // Is a default URL is set.
    if (UserConfigManager::getConfig()["global"]["default_url"]) {
        $default_url = UserConfigManager::getConfig()["global"]["default_url"];
    } else {
        $default_url = "http://" . $_SERVER['SERVER_NAME'];
    }

    // Is multi language is allowed.
    if (UserConfigManager::getConfig()["multi_language"]["allow_multi_language"]) {
        // Returns the default browser language.
        $language = MultiLanguage::getSelectedLanguage() . "/";
    } else {
        $language = "";
    }

    return $default_url . "/" . $language;
}

/**
 * Returns the raw URL (without protocol typing).
 * It is used for assets URLs (CSS and JS).
 *
 * E.g.: returns "//domain.com" instead of "https://domain.com"
 *
 * @return string
 */
function raw_url(): string
{
    return "//" . $_SERVER['SERVER_NAME'] . "/";
}

/**
 * Returns the current URL.
 *
 * @return string
 */
function current_url(): string
{
    return base_url() . $_SERVER['REQUEST_URI'] . "/";
}
