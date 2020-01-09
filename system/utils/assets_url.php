<?php

/**
 * Returns the image file absolute path.
 *
 * @param string $url The image URL path and name.
 *
 * @return string
 */
function img_url(string $url): string
{
    return raw_url() . "assets/img/" . $url;
}

/**
 * Returns the CSS file absolute path without protocol typing.
 *
 * @param string $url The CSS URL path and name.
 *
 * @return string
 */
function css_url(string $url): string
{
    return raw_url() . "assets/css/" . $url . ".css";
}

/**
 * Returns the JS file absolute path without protocol typing.
 *
 * @param string $url The JS URL path and name.
 *
 * @return string
 */
function js_url(string $url): string
{
    return raw_url() . "assets/js/" . $url . ".js";
}
