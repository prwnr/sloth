<?php

if (!function_exists('first_to_last')) {
    /**
     * Remove first element from array and puts it as last one
     * @param array $array
     * @return array
     */
    function first_to_last(array $array): array
    {
        $first = array_shift($array);
        $array[] = $first;
        return $array;
    }
}