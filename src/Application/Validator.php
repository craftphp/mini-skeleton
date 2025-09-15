<?php
namespace Craft\Application;

/**
 * #### Validator class for validating data.
 *
 * This class provides methods to validate various types of data.
 */
#region Validator
class Validator
{
    public static function required($value): bool
    {
        return !empty($value) || $value === '0';
    }

    public static function email($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function minLength($value, int $min): bool
    {
        return mb_strlen((string)$value) >= $min;
    }

    public static function maxLength($value, int $max): bool
    {
        return mb_strlen((string)$value) <= $max;
    }

    public static function numeric($value): bool
    {
        return is_numeric($value);
    }

    /**
     * Make a validation check on the provided data.
     * @param array $data Data to validate.
     * @param array $rules Validation rules in the format 'field' => 'rule1|rule2'.
     * @param array $messages Custom error messages in the format 'field.rule' => 'Error message'.
     * @return array[]
     */
    public static function make(array $data, array $rules, array $messages = []): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $ruleList = explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                $ruleName = $rule;
                $param = null;

                if (strpos($rule, ':') !== false) {
                    [$ruleName, $param] = explode(':', $rule, 2);
                }

                $valid = true;
                switch ($ruleName) {
                    case 'required':
                        $valid = self::required($value);
                        break;
                    case 'email':
                        $valid = self::email($value);
                        break;
                    case 'string':
                        $valid = is_string($value);
                        break;
                    case 'min':
                        $valid = self::minLength($value, (int) $param);
                        break;
                    case 'max':
                        $valid = self::maxLength($value, (int) $param);
                        break;
                    case 'numeric':
                        $valid = self::numeric($value);
                        break;
                    case 'array':
                        $valid = is_array($value);
                        break;
                }

                if (!$valid) {
                    $key = $field . '.' . $ruleName;
                    $errors[$field][] = $messages[$key] ?? "$field validation failed for $ruleName";
                }
            }
        }

        return $errors;
    }

}
#endregion