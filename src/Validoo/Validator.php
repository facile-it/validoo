<?php

namespace Validoo;

/**
 * Validator Class
 * @author Alessandro Manno <alessandro.manno@facile.it>
 * @author Chiara Ferrazza <chiara.ferrazza@facile.it>
 * @copyright (c) 2016, Facile.it
 * @license https://github.com/facile-it/validoo/blob/master/LICENSE MIT Licence
 * @link https://github.com/facile-it/validoo
 */

/**
 * TODO: Exception handling for rules with parameters
 * TODO: unit tests for numeric, float, alpha_numeric, max_length, min_length, exact_length
 * TODO: add protection filters for several input vulnerabilities.
 */
class Validator
{

    /** @var array */
    private $errors = [];
    /** @var array */
    private $namings = [];
    /** @var array */
    private $customErrorsWithInputName = [];
    /** @var array */
    private $customErrors = [];

    /**
     * Constructor is not allowed because Validoo uses its own
     * static method to instantiate the validation
     */
    private function __construct($errors, $namings)
    {
        $this->errors = $errors;
        $this->namings = $namings;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return (empty($this->errors) == true);
    }

    /**
     * @param array $errors_array
     */
    public function customErrors(array $errors_array)
    {
        foreach ($errors_array as $key => $value) {
            // handle input.rule eg (name.required)
            if (preg_match("#^(.+?)\.(.+?)$#", $key, $matches)) {
                // $this->customErrorsWithInputName[name][required] = error message
                $this->customErrorsWithInputName[(string)$matches[1]][(string)$matches[2]] = $value;
            } else {
                $this->customErrors[(string)$key] = $value;
            }
        }
    }

    /**
     * @return string
     */
    protected function getDefaultLang(): string
    {
        return "en";
    }

    /**
     * @param string $lang
     * @return null
     */
    protected function getErrorFilePath(string $lang)
    {
        return null;
    }

    /**
     * @param string|null $lang
     * @return array|mixed
     */
    protected function getDefaultErrorTexts(string $lang = null)
    {
        /* handle default error text file */
        $default_error_texts = [];
        if (file_exists(__DIR__ . "/errors/" . $lang . ".php")) {
            $default_error_texts = include(__DIR__ . "/errors/" . $lang . ".php");
        }
        return $default_error_texts;
    }

    /**
     * @param string|null $lang
     * @return array|mixed
     */
    protected function getCustomErrorTexts(string $lang = null)
    {
        /* handle error text file for custom validators */
        $custom_error_texts = [];
        if (file_exists($this->getErrorFilePath($lang)))
            $custom_error_texts = include($this->getErrorFilePath($lang));
        return $custom_error_texts;
    }

    /**
     * @param string $input_name
     * @return mixed|string
     */
    protected function handleNaming(string $input_name)
    {
        if (isset($this->namings[$input_name])) {
            $named_input = $this->namings[$input_name];
        } else {
            $named_input = $input_name;
        }
        return $named_input;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function handleParameterNaming(array $params)
    {
        foreach ($params as $key => $param) {
            if (preg_match("#^:([a-zA-Z0-9_]+)$#", $param, $param_type)) {
                if (isset($this->namings[(string)$param_type[1]]))
                    $params[$key] = $this->namings[(string)$param_type[1]];
                else
                    $params[$key] = $param_type[1];
            }
        }
        return $params;
    }

    /**
     * @param string|null $lang
     * @return array
     * @throws ValidooException
     */
    public function getErrors(string $lang = null): array
    {
        if ($lang == null)
            $lang = $this->getDefaultLang();

        $error_results = [];
        $default_error_texts = $this->getDefaultErrorTexts($lang);
        $custom_error_texts = $this->getCustomErrorTexts($lang);

        foreach ($this->errors as $input_name => $results) {
            foreach ($results as $rule => $result) {
                $named_input = $this->handleNaming($input_name);
                /**
                 * if parameters are input name they should be named as well
                 */
                $result['params'] = $this->handleParameterNaming($result['params']);
                var_dump($rule);
                // if there is a custom message with input name, apply it
                if (isset($this->customErrorsWithInputName[(string)$input_name][(string)$rule])) {
                    $error_message = $this->customErrorsWithInputName[(string)$input_name][(string)$rule];
                } // if there is a custom message for the rule, apply it
                else if (isset($this->customErrors[(string)$rule])) {
                    $error_message = $this->customErrors[(string)$rule];
                } // if there is a custom validator try to fetch from its error file
                else if (isset($custom_error_texts[(string)$rule])) {
                    $error_message = $custom_error_texts[(string)$rule];
                } // if none try to fetch from default error file
                else if (isset($default_error_texts[(string)$rule])) {
                    $error_message = $default_error_texts[(string)$rule];
                } else {
                    throw new ValidooException(ValidooException::NO_ERROR_TEXT, $rule);
                }
                /**
                 * handle :params(..)
                 */
                if (preg_match_all("#:params\((.+?)\)#", $error_message, $param_indexes))
                    foreach ($param_indexes[1] as $param_index) {
                        $error_message = str_replace(":params(" . $param_index . ")", $result['params'][$param_index], $error_message);
                    }
                $error_results[] = str_replace(":attribute", $named_input, $error_message);
            }
        }
        return $error_results;
    }

    /**
     * @param string $input_name
     * @param string|null $rule_name
     * @return bool
     */
    public function has(string $input_name, string $rule_name = null): bool
    {
        if ($rule_name != null)
            return isset($this->errors[$input_name][$rule_name]);
        return isset($this->errors[$input_name]);
    }

    /**
     * @return array
     */
    final public function getResults(): array
    {
        return $this->errors;
    }

    /**
     * Gets the parameter names of a rule
     * @param $rule
     * @return mixed
     */
    private static function getParams($rule)
    {
        if (preg_match("#^([a-zA-Z0-9_]+)\((.+?)\)$#", $rule, $matches)) {
            return [
                'rule' => $matches[1],
                'params' => explode(",", $matches[2])
            ];
        }
        return [
            'rule' => $rule,
            'params' => []
        ];
    }

    /**
     * Handle parameter with input name
     * eg: equals(:name)
     * @param mixed $params
     * @return mixed
     */
    private static function getParamValues($params, $inputs)
    {
        foreach ($params as $key => $param) {
            if (preg_match("#^:([a-zA-Z0-9_]+)$#", $param, $param_type)) {
                $params[$key] = @$inputs[(string)$param_type[1]];
            }
        }
        return $params;
    }

    /**
     * @param mixed $inputs
     * @param array $rules
     * @param array|null $naming
     * @return Validator
     * @throws ValidooException
     */
    public static function validate($inputs, array $rules, array $naming = null): self
    {
        $errors = null;
        foreach ($rules as $input => $input_rules) {
            if (is_array($input_rules)) {
                foreach ($input_rules as $rule => $closure) {
                    if (!isset($inputs[(string)$input]))
                        $input_value = null;
                    else
                        $input_value = $inputs[(string)$input];
                    /**
                     * if the key of the $input_rules is numeric that means
                     * it's neither an anonymous nor an user function.
                     */
                    if (is_numeric($rule)) {
                        $rule = $closure;
                    }
                    $rule_and_params = static::getParams($rule);
                    $params = $real_params = $rule_and_params['params'];
                    $rule = $rule_and_params['rule'];
                    $params = static::getParamValues($params, $inputs);
                    array_unshift($params, $input_value);
                    /**
                     * Handle anonymous functions
                     */
                    if (@get_class($closure) == 'Closure') {
                        $refl_func = new \ReflectionFunction($closure);
                        $validation = $refl_func->invokeArgs($params);
                    } /**
                     * handle class methods
                     */ else if (@method_exists(get_called_class(), $rule)) {
                        $refl = new \ReflectionMethod(get_called_class(), $rule);
                        if ($refl->isStatic()) {
                            $refl->setAccessible(true);
                            $validation = $refl->invokeArgs(null, $params);
                        } else {
                            throw new ValidooException(ValidooException::STATIC_METHOD, $rule);
                        }
                    } else {
                        throw new ValidooException(ValidooException::UNKNOWN_RULE, $rule);
                    }
                    if ($validation == false) {
                        $errors[(string)$input][(string)$rule]['result'] = false;
                        $errors[(string)$input][(string)$rule]['params'] = $real_params;
                    }
                }
            } else {
                throw new ValidooException(ValidooException::ARRAY_EXPECTED, $input);
            }
        }
        return new self($errors, $naming);
    }

    /**
     * @param null $input
     * @return bool
     */
    protected static function required($input = null): bool
    {
        return (!is_null($input) && (trim($input) != ''));
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function numeric($input): bool
    {
        return is_numeric($input);
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function email($input): bool
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function integer($input): bool
    {
        return is_int($input) || ($input == (string)(int)$input);
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function float($input): bool
    {
        return is_float($input) || ($input == (string)(float)$input);
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function alpha($input): bool
    {
        return (preg_match("#^[a-zA-ZÀ-ÿ]+$#", $input) == 1);
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function alpha_numeric($input): bool
    {
        return (preg_match("#^[a-zA-ZÀ-ÿ0-9]+$#", $input) == 1);
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function ip($input): bool
    {
        return filter_var($input, FILTER_VALIDATE_IP);
    }

    /*
     * TODO: need improvements for tel and urn urls.
     * check out url.test.php for the test result
     * urn syntax: http://www.faqs.org/rfcs/rfc2141.html
     *
     */
    /**
     * @param $input
     * @return bool
     */
    protected static function url($input): bool
    {
        return filter_var($input, FILTER_VALIDATE_URL);
    }

    /**
     * @param $input
     * @param $length
     * @return bool
     */
    protected static function max_length($input, $length): bool
    {
        return (strlen($input) <= $length);
    }

    /**
     * @param $input
     * @param $length
     * @return bool
     */
    protected static function min_length($input, $length): bool
    {
        return (strlen($input) >= $length);
    }

    /**
     * @param $input
     * @param $length
     * @return bool
     */
    protected static function exact_length($input, $length): bool
    {
        return (strlen($input) == $length);
    }

    /**
     * @param $input
     * @param $param
     * @return bool
     */
    protected static function equals($input, $param): bool
    {
        return ($input == $param);
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function is_path($input): bool
    {
        return is_dir($input);
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function is_filename($input): bool
    {
        return preg_match('/^[A-Za-z0-9-_]+[.]{1}[A-Za-z]+$/', $input);
    }
}
