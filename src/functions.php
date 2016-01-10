<?php

if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        return $headers;
    }
}

function array_remove_null($array) {
    foreach ($array as $key => $value)
    {
        if(is_null($value)) {
            unset($array[$key]);
        }
        if(is_array($value)) {
            $array[$key] = array_remove_null($value);
        }
    }
    return $array;
}