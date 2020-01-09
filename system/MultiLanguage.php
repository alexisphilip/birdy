<?php

// TODO: done
//  app/config autoload: add multi_language
//  MasterController: add loadTranslation
//  UserConfigListManager: add loadTranslation methods, and getTranslation... etc
//  MasterController: test if fetch is working from autoload and controller
//  MasterController::getFileLanguage() to getLanguageFiles() and add all files

// TODO: todo
//  MultiLanguage: fetch all files in getLanguageFiles() and _title.json
//  MasterController: check if variable creation needs to change or not
//  demo_view: test widget $variables

/**
 * Class MultiLanguage.
 */
class MultiLanguage
{
    /**
     * The selected language in 2 letter code. E.g.: fr, en, de...
     * @var string
     */
    static $selected_language;
    /**
     * The complete page's translation, as well as the title configuration.
     * @var object
     */
    static $translation;

    /**
     * Returns the supported languages from the user configuration.
     *
     * @return array The supported languages.
     */
    public static function getSupportedLanguages(): array
    {
        return UserConfigManager::getConfig()["multi_language"]["supported_languages"];
    }

    /**
     * Returns the main language of the browser.
     *
     * @returns string The main language of the browser.
     */
    public static function getBrowserLanguage(): string
    {
        return substr(locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);
    }

    /**
     * Returns the default language from the user configuration.
     *
     * @return string The default language from the user configuration.
     */
    public static function getDefaultLanguage(): string
    {
        return UserConfigManager::getConfig()["multi_language"]["default_language"];
    }

    /**
     * Checks if the language passed is supported (set in the user configuration).
     *
     * @param string $language The language in 2 letter code. E.g.: fr, en, de...
     * @return bool
     */
    public static function isSupported(string $language): bool
    {
        return in_array($language, self::getSupportedLanguages());
    }

    /**
     * Fetches and includes the language files.
     * Translate JSON data to PHP object array.
     * Sets the translation's array to the "translation" attribute of the class.
     * Returns the translation's object array.
     *
     * @param array $files All the translations files to fetch.
     * @return object The translations object array.
     */
    public static function getLanguageFiles(array $files): object
    {
        $translation = [];
        $selected_language = self::getSelectedLanguage();

        $files_to_fetch = $files;

        foreach ($files_to_fetch as $key => $file) {
            $file_path = "./app/translations/" . $selected_language . "/" . $file;

            // If the translation file exists.
            if (file_exists($file_path)) {
                $json_string = file_get_contents($file_path);
                $translation_content = json_decode($json_string);
                //$translation[$key] = $translation_content;
                $translation = (object) array_merge(
                    (array) $translation,
                    (array) $translation_content
                );
            } // If the file does not exists.
            else {
                if ($file !== "_title.json") {
                    // TODO: set error.
                    p("Error: \"" . $file . "\" must be defined and translated in \"" . $file_path . "\"", "error");
                    die;
                }
            }
        }

        self::setTranslation($translation);

        return $translation;
    }

    /**
     * Returns the selected language.
     *
     * @return string The selected language the website will display.
     */
    public static function getSelectedLanguage(): string
    {
        return self::$selected_language;
    }

    /**
     * Sets the selected language.
     *
     * @param string $selected_language The selected language the website will display.
     */
    public static function setSelectedLanguage(string $selected_language): void
    {
        self::$selected_language = $selected_language;
    }

    /**
     * Returns the translation.
     *
     * @return object The page's translation as well as the translated default title configuration.
     */
    public static function getTranslation(): object
    {
        return self::$translation;
    }

    /**
     * Sets the translation.
     *
     * @param object $translation The page's translation as well as the translated default title configuration.
     */
    public static function setTranslation(object $translation): void
    {
        self::$translation = $translation;
    }
}