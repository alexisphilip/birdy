<?php

/**
 * Class AutoLoader.
 */
class AutoLoader
{
    /**
     * Includes a population of files by name from an array and a base directory.
     *
     * @param string $directory          The directory's path.
     * @param array  $files_in_directory An array of the files names to be included.
     *
     * @throws Exception If a file cannot be included.
     */
    public static function includeFromDirectory(string $directory, array $files_in_directory): void
    {
        foreach ($files_in_directory as $key => $value) {
            $path = $directory . "/" . $value . ".php";
            require_exists($path);
        }
    }

    /**
     * /!\ DEPRECATED /!\ To fix later.
     *
     * Includes a whole directory.
     *
     * @param string $directory The directory's path.
     *
     * @throws Exception If a file cannot be included.
     */
    // TODO
    //  fix include: including all files but in the wrong place?
    public static function includeAllDirectory(string $directory): void
    {
        $directory .= "/*.php";

        foreach (glob($directory) as $filename) {
            require_once $filename;
        }
    }

    /**
     * /!\ DEPRECATED /!\ To fix later.
     * Old method call in "index.php" AutoLoader::autoLoad("application/config", ".*");
     *
     * Includes the regex matched files in a directory.
     *
     * @param string $path  The directory to scan.
     * @param string $regex The regular expression to perform on the files in the directory.
     *
     * @throws Exception If no files we matched.
     */
    // TODO
    //  fix include: including all files but in the wrong place?
    //  maybe use a better way to use regex?
    public static function autoLoad(string $path, string $regex): void
    {
        $files = array_slice(scandir($path), 2);
        $total_non_matched = 0;

        foreach ($files as $key => $value) {

            preg_match('/' . $regex . '/', $value, $matches, PREG_OFFSET_CAPTURE);

            if ($matches[0][0]) {
                require_exists($path . "/" . $matches[0][0]);
            } else {
                $total_non_matched++;
            }
        }

        // If no files were match, throw exception.
        if ($total_non_matched == count($files)) {
            throw new Exception("Regex did not match any file in \"" . $path . "\" directory.");
        }
    }
}
