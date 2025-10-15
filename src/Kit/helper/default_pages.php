<?php
if(!function_exists('get404pages')) {
    /**
     * Get 404 page content
     * @return bool|string
     */
    function get404pages() {
        return file_get_contents(getBaseUrl().Source::file('404.php'));
    }
}