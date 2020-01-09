<?php


/**
 * Class Router.
 */
class Router
{
    private $split;
    private $class;
    private $method;
    private $argument;
    private $controller_path = "./app/controller/";
    private $user_config;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        // Gets the user configuration.
        $this->user_config = UserConfigManager::getConfig();
    }

    /**
     * Routes the users query from the URL.
     *
     * @throws Exception
     */
    public function route(): void
    {
        // FORMATTING \\

        // Gets the current URL after domain name.
        // E.g.: /class/method/param1&param2=val1,val2
        $url = $_SERVER["REQUEST_URI"];

        // Splits it.
        // E.g.: ["", "class", "method", "param1&param2=val1,val2"]
        $this->split = explode("/", $url);

        // Deletes "" empty string at the start of array.
        array_shift($this->split);

        // If there is an empty string at the last position.
        // E.g. from the url: domain.com/class/        <- empty string
        // E.g. from the url: domain.com/class/method/ <- empty string
        if ($this->split[count($this->split) - 1] == "") {
            // Deletes it.
            array_pop($this->split);
        }


        // MULTI-LANGUAGE \\

        // If multi language is activated.
        if (UserConfigManager::getConfig()["multi_language"]["allow_multi_language"]) {
            $this->multiLanguage();
        }

        // NAME ATTRIBUTION \\

        // CLASS:
        // Case 1: no argument, then default controller.
        if ($this->split[0] == "") {
            // If the default controller set.
            if ($this->user_config["route"]["controller_default"]) {
                // Assign the default controller from config as the controller.
                $this->class = $this->user_config["route"]["controller_default"];
            }
            // If the default controller is not set.
            else {
                // Assign "Index" as controller.
                $this->class = "Index";
            }
        } // Case 2: there is an argument, then it will be the controller.
        else {
            $this->class = $this->split[0];
        }

        // METHOD:
        // Case 1: at least two arguments.
        // It means the second argument is a defined method.
        if (count($this->split) > 1 && $this->split[1] !== "") {
            $this->method = $this->split[1];
        } // Case 2: no argument is given.
        else {
            // Routing to the default method set by the user.
            if ($this->user_config["route"]["controller_default_method"]) {
                $this->method = $this->user_config["route"]["controller_default_method"];
            } // Routing to "index" method.
            else {
                $this->method = "index";
            }
        }

        // PARAMETER(S):
        // If there is a controller, a method and another value in "$this->split", these are parameters.
        if (count($this->split) > 2) {
            // Formats the parameters and returns then in an associative array.
            $this->argument = $this->formatParameters($this->split[2]);
        }


        // NAME FORMATTING \\

        // Formats the class and method names to pascal and camel, with hyphen support.
        $this->class = $this->formatClassAndMethodNames($this->class, "pascal");
        $this->method = $this->formatClassAndMethodNames($this->method, "camel");


        // INSTANTIATION \\

        $path = $this->controller_path . $this->class . ".php";

        // If the controller file exists.
        if (file_exists($path)) {

            // If it exists, include it (class).
            require_exists($path);

            // If the class exists.
            if (class_exists($this->class)) {

                // Instantiating the class.
                $obj_instance = new $this->class();

                // If the method exists.
                if (method_exists($obj_instance, $this->method)) {

                    $method = $this->method;

                    // Calls the object's method and passes arguments to it.
                    $obj_instance->$method($this->argument);
                } // If the method does not exist (wrong method in URL).
                else {
                    $this->callError("method");
                }
            } // If the class does not exist.
            else {
                $this->callError("class");
            }
        } // If the controller file does not exist (wrong controller in URL).
        else {
            $this->callError("controller");
        }
    }

    /**
     * Sets the language from the URL or from the browser or from the user configuration.
     * Formats the URl by deleting the language code from it (if it is present) so the router
     * can interpret the URL as "controller/method/parameters".
     *
     * @throws Exception
     */
    // TODO: make these error verifications in MultiLanguage class?
    private function multiLanguage(): void
    {
        require_exists("system/MultiLanguage.php");

        // If there are no supported languages precised in the user configuration.
        if (count(MultiLanguage::getSupportedLanguages()) === 0) {
            $this->callError("supported_languages_undefined");
        }
        // If the default language is not set.
        if (!MultiLanguage::getDefaultLanguage()) {
            $this->callError("default_language_not_set");
        }
        // If the default language is not in the supported list.
        if (!MultiLanguage::isSupported(MultiLanguage::getDefaultLanguage())) {
            $this->callError("default_language_not_in_supported_list");
        }

        // IMPORTANT: contains the first part of the URL.
        // E.g. from the URL: domain.tld/en <- here.
        $url_language = strtolower($this->split[0]);

        // If the first URL part is the language and is in the supported language list.
        // E.g. from the URL: domain.com/fr/class/...
        if (MultiLanguage::isSupported($url_language)) {
            // Removes language from url array (the first element) so the router can
            // route from what's after the language (e.g.: [fr](removed)/class/method).
            array_shift($this->split);

            // Sets the selected language in the MultiLanguage static class.
            MultiLanguage::setSelectedLanguage($url_language);
        }
        // If the first part of the URL is not in the supported language list, it could be:
        //  - a wrong/unsupported language name (e.g.: domain.tld/nl/class)
        //  - a valid class name / 404 error    (e.g.: domain.tld/class)
        else {
            // TODO: read below.
            //  IMPORTANT: this class verification is an almost duplicate of the controller.
            //  find a solution to make the code lighter.
            //  maybe create methods to try out code like (test class (is class null, etc), test method...)

            // If there is no argument, then default controller.
            if ($this->split[0] == "") {
                // If the default controller is not set
                if (!$this->user_config["route"]["controller_default"]) {
                    $this->split[0] = "Index";
                }
                $this->split[0] = $this->user_config["route"]["controller_default"];
            }

            $class_file = $this->formatClassAndMethodNames($this->split[0], "pascal");
            $class_file_url = $path = $this->controller_path . $class_file . ".php";

            $browser_language = MultiLanguage::getBrowserLanguage();
            $default_language = MultiLanguage::getDefaultLanguage();

            // If the file exists.
            $file_exists = false;
            if (file_exists($class_file_url)) {
                $file_exists = true;
            }

            // If it's not a class (file does not exist) it can be:
            //  - a 404 error
            //  - a wrong/unsupported language name
            // Here we will treat it as a wrong/unsupported language name.

            // If: the first position of the array is:
            //  - not a class (404 error, the file does not exist) AND
            //  - is 2 letters long (a wrong/unsupported language name)
            //
            // we can safely delete the first element of the URL array:
            // E.g.: ["fr", "controller", "method"]
            // Here, "fr" is a wrong/unsupported language, we need to make a redirection.
            // "fr" is deleted from the array, and the URL is "glued" together to form the
            // URL redirection.

            // Note: if it's actually not a wrong/unsupported language code (could just be a
            // short controller name, less than 2 characters long), it's a 404 error.
            // Then the next part of the router will take care of it.
            if (!$file_exists && strlen($this->split[0]) == 2) {
                array_shift($this->split);
            }
            $url = implode("/", $this->split);

            // So, check if the browser language is in the supported list.
            if (MultiLanguage::isSupported($browser_language)) {
                MultiLanguage::setSelectedLanguage($browser_language);
            }
            // If the browser language is not in the supported list, the default language is selected.
            // The default language always exists since it has been verified at the start of the multi
            // language checking.
            else {
                MultiLanguage::setSelectedLanguage($default_language);
            }

            // Redirects to the same page with default browser/settings language.
            // E.g. input:       domain.tld/nl/controller   <- unsupported language.
            // E.g. redirection: domain.tld/en/controller   <- supported language.
            header('Location: ' . base_url() . $url);
        }

        // If it's a valid file and class, the router will route the normally.
    }

    /**
     * Formats the parameter string from the URL into a PHP associative array.
     *
     * Input: "val&key1=val&key2=val1,val2"
     * Output: Array(
     *      [0] => "val"
     *      [key1] => "val"
     *      [key2] => Array(
     *          [0] => val1,
     *          [1] => val2
     *      )
     * )
     *
     * @param string $parameters_string The parameter string from the URL.
     * @return array An associative array containing the keys and values.
     */
    private function formatParameters(string $parameters_string): array
    {
        // Will contains all the parameters formatted.
        $parameters = [];

        $parameters_joined = explode("&", $parameters_string);

        foreach ($parameters_joined as $parameter_joined) {

            $parameter = explode("=", $parameter_joined);

            // If it is only a value.
            // E.g.: method/5
            if (count($parameter) == 1) {
                // Push the param value as array value, the key will be auto incremented.
                $parameters[] = $parameter[0];
            }
            // If it is a key and value(s).
            // E.g.: method/key=
            // E.g.: method/key=val
            // E.g.: method/key=val1,val2,val3
            else if (count($parameter) == 2) {

                $values = explode(",", $parameter[1]);

                // If there is one value.
                if (count($values) == 1) {
                    $parameters[$parameter[0]] = $values[0];
                } // If there are multiple values, put it in an array.
                else {
                    foreach ($values as $value) {
                        $parameters[$parameter[0]][] = $value;
                    }
                }
            }
            // If the URL param contains an error.
            // E.g.: method/key=val2=val2
            else {
                // TODO: Error
                print_r("Error in the parameter formatting. Check the URL.");
                die;
            }
        }

        return $parameters;
    }

    /**
     * Formats the string from a class name or method name to PascalCase or camelCase.
     * Also works with hyphens: any string containing hyphens will be exploded and concatenated.
     *
     * Input:  "class-name-to-pascal", "pascal"
     * Output: "ClassNameToPascal"
     *
     * Input:  "method-name-to-camel", "camel"
     * Output: "methodNameToCamel"
     *
     * @param string $string_to_format The string which needs to be formatted.
     * @param string $type The type of string to format to: "pascal", "camel".
     * @return string The formatted string.
     */
    private function formatClassAndMethodNames(string $string_to_format, string $type): string
    {
        $formatted_string = "";

        // If the string contains one or multiple hyphens.
        if (preg_match("/-/i", $string_to_format)) {

            $split_element = explode("-", $string_to_format);

            // If it's a class, then convert to PascalCase.
            // URL:   "hyphen-class-to-pascal"
            // class: "HyphenClassToPascal"
            if ($type == "pascal") {
                for ($i = 0; $i < count($split_element); $i++) {
                    $formatted_string .= ucfirst(strtolower($split_element[$i]));
                }
            }
            // If it's a method, then convert to camelCase.
            // URL:   "hyphen-method-to-camel"
            // class: "hyphenMethodToCamel"
            else if ($type == "camel") {
                $formatted_string .= strtolower($split_element[0]);
                for ($i = 1; $i < count($split_element); $i++) {
                    $formatted_string .= ucfirst(strtolower($split_element[$i]));
                }
            }
        } // If it does not contain hyphens.
        else {
            if ($type == "pascal") {
                $formatted_string = ucfirst(strtolower($string_to_format));
            } else if ($type == "camel") {
                $formatted_string = strtolower($string_to_format);
            }
        }

        return $formatted_string;
    }

    /**
     * Throws default or custom errors.
     *
     * @param string $type The object of the error: "controller_default", "controller" or "method".
     * @throws Exception
     */
    private function callError(string $type): void
    {
        // If the error controller is set in the user configuration.
        // It will not display any errors coming from the router, so the user gets a custom 404 error page.
        if (!$this->user_config["route"]["display_router_errors"]) {
            $path = $this->controller_path . "Errors.php";

            require_exists($path);

            $error = new Errors();
            $error->error404();
        } // If the error controller is not set, display errors.
        else {
            // If the controller file does not exist.
            if ($type == "controller") {
                // TODO: set error.
                p("Error: \"" . $this->class . "\" controller file does not exist.", "error");
            } // If the class does not exist.
            else if ($type == "class") {
                // TODO: set error.
                p("Error: \"" . $this->class . "\" class does not exist but \"" . $this->class . "\" controller file exists. Make sure the class name matches the file name.", "error");
            } // If the method does not exist.
            else if ($type == "method") {
                // TODO: set error.
                p("Error: \"" . $this->method . "\" method in \"" . $this->class . "\" controller does not exist.", "error");
            } // If supported_languages array is empty.
            else if ($type == "supported_languages_undefined") {
                // TODO: set error.
                p("Error: multi language support is activated but supported language are not defined in \"app/config/multi_language::\$multi_language[\"supported_languages\"]\"", "error");
            } // If default language is not set.
            else if ($type == "default_language_not_set") {
                // TODO: set error.
                p("Error: multi language support is activated but default language is not defined in \"app/config/multi_language::\$multi_language[\"default_language\"]\"", "error");
            } // If default language is not set.
            else if ($type == "default_language_not_in_supported_list") {
                // TODO: set error.
                p("Error: multi language support is activated but default language defined in \"app/config/multi_language::\$multi_language[\"default_language\"]\" is not in the supported languages list defined in \"app/config/multi_language::\$multi_language[\"supported_languages\"]\"", "error");
            }
            die;
        }
    }
}
