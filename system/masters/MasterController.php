<?php

/**
 * Class MasterController.
 *
 * Master controller the highest parent of all controllers.
 * It allows to load files, models and a template.
 */
class MasterController extends Core
{
    private $title = "";
    /**
     * Defines if the $title will overwrite the default title (as well as its prefix and suffix).
     * @var bool
     */
    private $title_overwrite = false;
    private $title_prefix = "";
    private $title_suffix = "";

    /**
     * Set the current's page title.
     */
    public function setTitle()
    {
        $args = func_get_args();

        // If "true" is passed as a second argument, the title will overwrite the default title settings.
        if ($args[1] === True) {
            $this->title = $args[0];
            $this->title_overwrite = $args[1];
        } // Otherwise the title will be concatenated respecting the settings if they are set.
        else {
            $this->title = $args[0];
        }
    }

    /**
     * Sets the title prefix.
     *
     * @param string $prefix The title's prefix.
     */
    public function setTitlePrefix($prefix)
    {
        $this->title_prefix = $prefix;
    }

    /**
     * Sets the title's suffix.
     *
     * @param string $suffix The title's suffix.
     */
    public function setTitleSuffix($suffix)
    {
        $this->title_suffix = $suffix;
    }

    /**
     * Sets CSS file(s).
     */
    public function loadCSS()
    {
        UserConfigListManager::addToIncludeListFromName("css", func_get_args());
    }

    /**
     * Sets JS file(s).
     */
    public function loadJS()
    {
        UserConfigListManager::addToIncludeListFromName("js", func_get_args());
    }

    /**
     * Sets helper file(s).
     */
    public function loadHelpers()
    {
        UserConfigListManager::addToIncludeListFromName("helpers", func_get_args());
    }

    /**
     * Sets translation file(s).
     */
    public function loadTranslations()
    {
        UserConfigListManager::addToIncludeListFromName("translations", func_get_args());
    }

    /**
     * Loads a model from the given parameter.
     *
     * @param string $model_name The models name.
     * @return mixed
     */
    public function loadModel($model_name)
    {
        // TODO
        //  declare utils for model path, and helper path?
        $view_file = "./app/model/" . $model_name . ".php";

        require_once $view_file;

        // Return the instance of the model so the view can use it.
        $model = new $model_name();

        return $model;
    }

    /**
     * Loads the a view and the corresponding template from the given parameter.
     *
     * @param string $view_name The given view name.
     * @param mixed $template_name The given template name (optional) or the data (optional).
     * @param array $data Data passed to the view (optional).
     */
    // TODO: load the helpers.
    public function view($view_name, $template_name = null, array $data = null)
    {
        $multi_language = false;

        $user_config = UserConfigManager::getConfig();

        // Arguments treatment \\

        // Gets the arguments.
        $args = func_get_args();

        // If only one argument: it is a the view name.
        if (count($args) == 1) {
            // Checks if the default template is set in the user configuration and returns it.
            $template_name = $this->isDefaultTemplateSet();
            $view_name = $args[0];
        } // If two arguments, the second could be the template name or the data.
        else if (count($args) == 2) {
            $view_name = $args[0];

            // If its the template name.
            if (gettype($args[1]) == "string") {
                $template_name = $args[1];
            } // If its the data.
            else if (gettype($args[1]) == "array") {
                // Checks if the default template is set in the user configuration and returns it.
                $template_name = $this->isDefaultTemplateSet();
                $data = $args[1];
            } // Otherwise, error.
            else {
                // TODO: set error.
                p("Error: the second argument of the \"template()\" method must be the template name, an array of data, or set to blank.", "error");
            }
        } // If 3 arguments, first is the view, second is the template name, third is the data.
        else if (count($args) == 3) {
            $view_name = $args[0];
            $template_name = $args[1];
        }

        // MULTI LANGUAGE
        // If multi language is allowed.
        if (UserConfigManager::getConfig()["multi_language"]["allow_multi_language"]) {
            $multi_language = true;

            // $translation_files will contain all the translation files to be loaded, as well as the view name and the default title configuration.
            $translation_files = UserConfigListManager::getNameTranslations();
            $translation_files[] = $view_name . ".json";
            $translation_files[] = "_title.json";

            $language_code = MultiLanguage::getSelectedLanguage();

            // This method will load all the translation files.
            // The translation of the page will be set in "MultiLanguage"
            // static class, it can easily be accessible now.
            MultiLanguage::getLanguageFiles($translation_files);
        } else {
            $language_code = "en";
        }

        /*
         * IMPORTANT about TITLE:
         *
         * /!\ Global title are normally set through the user configuration (app/config/title.php)
         * /!\ Custom page title are set through the page controller.
         *
         * STEP 1 - (In the controller) these page title are directly added to MasterController attributes.
         * STEP 2 - If a title is not set (prefix, title or suffix), the default one (from the user config)
         *          will be automatically set.
         *
         * STEP 3 - If multi language is added, default titles from "translations/{lang}/_title.json"
         *          will overwrite the titles.
         * STEP 4 - Also, if custom page title are set through the page's translation, these will overwrite
         *          the default's title translation.
         *
         * STEP 5 - Concatenate the prefix, the title and the suffix. If $this->overwrite is set to true,
         *          only the title will be displayed (prefix and suffix set to blank).
         */

        // STEP 2
        // Sets the unset values with title config from user config.
        $title_config = $user_config["title"];

        // If the current page title is not set by the user, use the default title, same for prefix and suffix.
        if (!$this->title) $this->setTitle($title_config["default"]);
        if (!$this->title_prefix) $this->setTitlePrefix($title_config["default_prefix"]);
        if (!$this->title_suffix) $this->setTitleSuffix($title_config["default_suffix"]);


        // Multi language title configuration.
        //  - set title from "_title.json" translation file and from the "{view_translation}.json::config title file.
        //  - create dynamic variables for each elements in the corresponding JSON file.
        if ($multi_language) {

            // STEP 3
            // Overwrites the default title config (from app/config/title.php) if a configuration is set (in "_title.json").
            // /!\ this overwrites the default title set in the user configuration (app/config/title.php).
            if (MultiLanguage::getTranslation()->_title) {
                $default_title_config = MultiLanguage::getTranslation()->_title->config;

                if ($default_title_config->default_title) $this->setTitle($default_title_config->default_title);
                if ($default_title_config->default_title_prefix) $this->setTitlePrefix($default_title_config->default_title_prefix);
                if ($default_title_config->default_title_suffix) $this->setTitleSuffix($default_title_config->default_title_suffix);
            }

            // STEP 4
            // Sets the page's translation title config.
            $page_title_config = MultiLanguage::getTranslation()->{$view_name}->config;

            if ($page_title_config->title) $this->setTitle($page_title_config->title, $page_title_config->title_overwrite);
            if ($page_title_config->title_prefix) $this->setTitlePrefix($page_title_config->title_prefix);
            if ($page_title_config->title_suffix) $this->setTitleSuffix($page_title_config->title_suffix);
        }

        // STEP 5
        // Sets the page title (<title> tag in <head>).
        // If it is set to "overwrite", prints just the title.
        if ($this->title_overwrite) {
            $page_title = $this->title;
        } // Otherwise print the title and prefix/suffix.
        else {
            $page_title = $this->title_prefix . $this->title . $this->title_suffix;
        }

        // Data dynamic variable creation \\

        // If data is passed to the view.
        if ($data) {
            // For each key of the $data array, create a var.
            foreach ($data as $key => $value) {

                // If the key is empty or has the wrong type (e.g.: $data[] = "value with no key")
                // it throws an error.
                if (empty($key) || gettype($key) !== "string") {

                    // If the key is empty.
                    if (empty($key)) {
                        // TODO: set error.
                        p("Error: no key is set for the value \"" . $value . "\" in the data passed in the \"view()\" method.", "error");
                    } // If the type is not a string.
                    else {
                        // TODO: set error.
                        p("Error: the type of the value \"" . $value . "\" in the data passed to the \"view()\" method must be a string: " . gettype($key) . " given.", "error");
                    }
                    die;
                }

                // Creates the variable dynamically.
                ${$key} = $value;
            }
        }

        // If multi language is allowed.
        if ($multi_language) {

            // 1 - Gets the whole translation: the main page's translation and the other translated elements (widgets, forms...).
            $complete_translation = MultiLanguage::getTranslation();

            // 2 - Adds the translated view data to the array.
            // Example of usage in the view: $title, $section1->title_section
            $translated_view_data = $complete_translation->{$view_name}->content;

            // 3 - Deletes the default language title configuration array and the
            // view translation from the whole translation array.
            unset($complete_translation->_title);
            unset($complete_translation->{$view_name});

            // 4 - Now in the $complete_translation array is left all the other translations.
            // Adds all the other translation with the key (widgets, forms...).
            // Example of usage in the view: $widget->title, $widget->content
            foreach ($complete_translation as $translation_name => $translation_content) {
                // Here the key name is set to the translation name.
                // E.g: $widget / $form...
                $translated_view_data->{$translation_name} = $translation_content;
            }

            // From the page translation, create a variable from each direct children key name
            // so the data can be accessed directly with variables.
            foreach ($translated_view_data as $key => $value) {
                ${$key} = $value;
            }
        }


        // Assets inclusion \\

        // These variables and arrays are used in the template.
        $css_urls = UserConfigListManager::getNameCss();
        $js_urls = UserConfigListManager::getNameJs();

        // Includes the helpers.
        foreach (UserConfigListManager::getNameHelpers() as $helper_name) {
            $helper_path = "./app/helpers/" . $helper_name . ".php";
            if (!file_exists($helper_path)) {
                // TODO: set error.
                p("Error: the helper \"" . $helper_name . ".php\" in \"app/helpers\" you are trying to include does not exist.", "error");
            } else {
                require_once $helper_path;
            }
        }

        // File integrity verification \\

        // Checks and returns the path of the view.
        $view_path = $this->isExists("./app/view/", $view_name, "file");

        // Checks and returns the path of the template directory.
        $template_directory = $this->isExists("./app/view/templates/", $template_name, "directory");

        // Verifies if "template.php" exists in the template directory.
        if (!file_exists($template_directory . "/template.php")) {
            p("Error: \"template.php\" file does not exist in \"" . $template_directory . "/\". It is a required file that the view engine will call. Create it and call your view in it.", "error");
            die;
        }
        $template_path = $template_directory . "/template.php";

        require_once $template_path;
    }

    /**
     * Checks if the default template is set in the user configuration file.
     * If not, throws an error.
     *
     * @return string The default template.
     */
    private function isDefaultTemplateSet()
    {
        $user_config = UserConfigManager::getConfig();

        // If the default template is not set in the user configuration.
        if (!$user_config["global"]["default_template"]) {
            // TODO: set error.
            p("Error: since no default template is passed to the \"view()\" method, one must declared in \"app/config/global::\$global[\"default_template\"]\"", "error");
            die;
        } // If it is set, return it.
        else {
            $template_path = "./app/view/templates/" . $user_config["global"]["default_template"] . "/";

            // If it does not exist.
            if (!is_dir($template_path)) {
                // TODO: set error.
                p("Error: \"" . $user_config["global"]["default_template"] . "\" default template set in \"app/config/global::\$global[\"default_template\"]\" does not exist in \"app/view/templates/\".", "error");
                die;
            } // If it exists, returns it.
            else {
                return $user_config["global"]["default_template"];
            }
        }
    }

    /**
     * Checks if a file exists, returns it, or throws a specific error.
     *
     * @param string $dir The base directory where the file or directory is going to be checked.
     * @param string $file_or_folder The file name or directory name to check.
     * @param string $type The type of entity to check (file or directory).
     * @return string
     */
    private function isExists($dir, $file_or_folder, $type)
    {
        // If it's a file (not directory), add ".php" extension name to it.
        $type == "file" ? $if_file = ".php" : $if_file = "";

        $dir_to_check = $dir . $file_or_folder . $if_file;

        // If the directory does not exist.
        if (!file_exists($dir_to_check)) {

            // If it's a directory.
            if ($type == "directory") {
                // TODO: set error.
                p("Error: \"" . $file_or_folder . "\" directory name passed to the \"view()\" method does not exist in directory: \"" . $dir . "\".", "error");
            } // If it's a file.
            else if ($type == "file") {
                // TODO: set error.
                p("Error: \"" . $file_or_folder . "\" file name passed to the \"view()\" method does not exist in directory: \"" . $dir . "\"", "error");
            }
            die;
        } else {
            return $dir_to_check;
        }
    }
}
