<?php

/**
 * Prints in a formatted way any PHP data passed to it.
 *
 * @param mixed $data The data to be printed.
 */
function p($data, $type="default"): void
{
    // Default printing.
    if ($type == "default") {
        $tag = "pre";
        $background = "#FCFCFC";
        $color = "#050505";
        $emoji = "1F642";
    } // Error printing
    else if ($type == "error") {
        $tag = "p";
        $background = "#F57A73";
        $color = "black";
        $emoji = "1F915";
    }

    $style = "
        background: " . $background . ";
        color: " . $color . ";
        padding: 15px;
        margin: 15px;
        font-family: Consolas, sans-serif;
        font-size: 15px;
        box-shadow: 0px 0px 10px rgba(0,0,0,0.5);
    ";
    print_r("<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head>");
    print_r("<" . $tag . " style=\"" . $style . "\">");
    print_r("&#x" . $emoji . " ");
    print_r($data);
    print_r("</" . $tag . ">");
}
