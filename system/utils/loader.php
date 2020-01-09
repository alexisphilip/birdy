<?php

/**
 * Tries to include a file with "require_once".
 *
 * @param string $file File path to be included.
 * @throws Exception If the file could not be loaded.
 */
// TODO
//  make function work on MasterController->template() and template.php includes.
function require_exists(string $file): void
{
    // If the file exists.
    if (file_exists($file)) {
        require_once $file;
    }
    // If the file does not exist.
    else {
        //throw new Exception("File \"" . $file . "\" could not be included (with require_once()).");
        p("ERROR: File \"" . $file . "\" could not be included (with require_once()).", "error");
        die;
    }
}

/**
 * Include a whole directory.
 *
 * @param string $directory Directory path to be included.
 *
 * @throws Exception If the file could not be loaded.
 */
// TODO
//  fix include: including all files but in the wrong place?
function includeAllDirectory(string $directory): void
{
    $directory .= "/*.php";

    foreach (glob($directory) as $filename) {
        require_once $filename;
    }
}
