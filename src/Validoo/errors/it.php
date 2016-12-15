<?php
// TODO to translate
/**
 * :attribute => input name
 * :params => rule parameters ( eg: :params(0) = 10 of max_length(10) )
 */
return [
    'required' => ':attribute field is required',
    'integer' => ':attribute deve essere un intero',
    'float' => ':attribute deve essere un float',
    'numeric' => ':attribute deve essere numerico',
    'email' => ':attribute deve essere un email valida',
    'alpha' => ':attribute field must be an alpha value',
    'alpha_numeric' => ':attribute field must be alphanumeric',
    'ip' => ':attribute must contain a valid IP',
    'url' => ':attribute must contain a valid URL',
    'max_length' => ':attribute can be maximum :params(0) character long',
    'min_length' => ':attribute must be minimum :params(0) character long',
    'exact_length' => ':attribute field must :params(0) character long',
    'equals' => ':attribute field should be same as :params(0)'
];
