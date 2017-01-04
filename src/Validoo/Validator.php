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
     * @param $errors
     * @param $namings
     */
    private function __construct($errors, $namings)
    {
        $this->errors = $errors;
        $this->namings = $namings;
    }

    /**
     * @param $inputs
     * @param array $rules
     * @param array|null $naming
     * @return Validator
     * @throws ValidooException
     */
    public static function validate($inputs, array $rules, array $naming = null): self
    {
        $errors = null;
        foreach ($rules as $input => $input_rules) {

            if (is_string($input_rules))
                $input_rules = explode("|", $input_rules);

            if (!is_array($input_rules))
                throw new ValidooException(ValidooException::ARRAY_EXPECTED, $input);

            if (in_array("onlyifset", $input_rules) && !isset($inputs[$input]))
                continue;

            foreach ($input_rules as $rule => $closure) {
                if (!isset($inputs[$input])) {
                    $input_value = null;
                } else {
                    $input_value = $inputs[$input];
                }
                if (is_numeric($rule)) {
                    $rule = $closure;
                }
                if ('onlyifset' == $rule)
                    continue;

                $rule_and_params = self::getParams($rule);
                $params = $real_params = $rule_and_params['params'];
                $rule = $rule_and_params['rule'];
                $params = self::getParamValues($params, $inputs);
                array_unshift($params, $input_value);

                if (false == self::doValidation($closure, $params, $rule)) {
                    $errors[$input][$rule]['result'] = false;
                    $errors[$input][$rule]['params'] = $real_params;
                }
            }

        }
        return new self($errors, $naming);
    }

    /**
     * @param $closure
     * @param $params
     * @param $rule
     * @return mixed
     * @throws ValidooException
     */
    private static function doValidation($closure, $params, $rule)
    {
        if (@get_class($closure) === 'Closure') {
            $refl_func = new \ReflectionFunction($closure);
            $validation = $refl_func->invokeArgs($params);
        } else if (@method_exists(get_called_class(), $rule)) {
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
        return $validation;
    }

    /**
     * Gets the parameter names of a rule
     * @param $rule
     * @return mixed
     */
    private static function getParams($rule)
    {
        if (preg_match("#^([\w]+)\((.+?)\)$#", $rule, $matches)) {
            return [
                'rule' => $matches[1],
                'params' => explode(',', $matches[2])
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
     * @param $inputs
     * @return mixed
     */
    private static function getParamValues($params, $inputs)
    {
        foreach ($params as $key => $param) {
            if (preg_match("#^:([\w]+)$#", $param, $param_type)) {
                $params[$key] = @$inputs[(string)$param_type[1]];
            }
        }
        return $params;
    }

    /**
     * @param null $input
     * @return bool
     */
    protected static function required($input = null): bool
    {
        if (is_string($input))
            $input = trim($input);
        return (null !== $input && !empty($input));
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
    protected static function isdir($input): bool
    {
        return is_dir($input);
    }

    /**
     * @param $input
     * @return bool
     */
    protected static function isarray($input): bool
    {
        return is_array($input);
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
    protected static function is_filename($input): bool
    {
        return preg_match('/^[A-Za-z0-9-_]+[.]{1}[A-Za-z]+$/', $input);
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return empty($this->errors);
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
     * @param string|null $lang
     * @return array
     * @throws ValidooException
     */
    public function getErrors(string $lang = null): array
    {
        if (null === $lang) {
            $lang = $this->getDefaultLang();
        }

        $error_results = [];
        $default_error_texts = $this->getDefaultErrorTexts($lang);

        foreach ($this->errors as $input_name => $results) {
            foreach ($results as $rule => $result) {
                $named_input = $this->handleNaming($input_name);
                /**
                 * if parameters are input name they should be named as well
                 */
                $result['params'] = $this->handleParameterNaming($result['params']);
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
                if (preg_match_all("#:params\((.+?)\)#", $error_message, $param_indexes)) {
                    foreach ($param_indexes[1] as $param_index) {
                        $error_message = str_replace(':params(' . $param_index . ')', $result['params'][$param_index], $error_message);
                    }
                }
                $error_results[] = str_replace(':attribute', $named_input, $error_message);
            }
        }

        return $error_results;
    }

    /**
     * @return string
     */
    protected function getDefaultLang(): string
    {
        return 'en';
    }

    /*
     * TODO: need improvements for tel and urn urls.
     * check out url.test.php for the test result
     * urn syntax: http://www.faqs.org/rfcs/rfc2141.html
     *
     */

    /**
     * @param string|null $lang
     * @return array|mixed
     */
    protected function getDefaultErrorTexts(string $lang = null)
    {
        /* handle default error text file */
        $default_error_texts = [];
        if (file_exists(__DIR__ . '/errors/' . $lang . '.php')) {
            /** @noinspection PhpIncludeInspection */
            $default_error_texts = include __DIR__ . '/errors/' . $lang . '.php';
        }
        if (file_exists(__DIR__ . '/errors/' . $lang . '.json')) {
            $default_error_texts = json_decode(file_get_contents(__DIR__ . '/errors/' . $lang . '.json'), true);
        }


        return $default_error_texts;
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
            if (preg_match("#^:([\w]+)$#", $param, $param_type)) {
                if (isset($this->namings[(string)$param_type[1]])) {
                    $params[$key] = $this->namings[(string)$param_type[1]];
                } else {
                    $params[$key] = $param_type[1];
                }
            }
        }

        return $params;
    }

    /**
     * @param string $input_name
     * @param string|null $rule_name
     * @return bool
     */
    public function has(string $input_name, string $rule_name = null): bool
    {
        if (null !== $rule_name) {
            return isset($this->errors[$input_name][$rule_name]);
        }

        return isset($this->errors[$input_name]);
    }

    /**
     * @return array
     */
    final public function getResults(): array
    {
        return $this->errors;
    }
}
