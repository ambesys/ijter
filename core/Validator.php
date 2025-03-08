<?php
// core/Validator.php

class Validator
{
    private $data;
    private $errors = [];
    private $pdo;


    public function __construct($data, $pdo = null)
    {
        $this->data = $data;
        $this->pdo = $pdo;
    }

    public function validate($rules)
    {
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    $rule = explode(':', $rule);
                    $ruleName = $rule[0];
                    $ruleValue = $rule[1] ?? null;

                    $this->$ruleName($field, $ruleValue);
                }
            }
        }

        return empty($this->errors);
    }

    public function required($field)
    {
        $value = $this->data[$field] ?? '';
        if (empty($value)) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }

    public function email($field)
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Invalid email format';
        }
    }

    public function min($field, $length)
    {
        $value = $this->data[$field] ?? '';
        if (strlen($value) < $length) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $length . ' characters';
        }
    }

    public function match($field, $matchField)
    {
        $value = $this->data[$field] ?? '';
        $matchValue = $this->data[$matchField] ?? '';

        if ($value !== $matchValue) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' does not match';
        }
    }

    // core/Validator.php

    public function unique($field, $table)
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && $this->pdo) {
            // Map form field names to database column names
            $columnMap = [
                'email' => 'user_email',
                // Add other mappings as needed
            ];

            // Get the correct column name
            $column = $columnMap[$field] ?? $field;

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
            $stmt->execute([$value]);

            if ($stmt->fetchColumn() > 0) {
                $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' already exists';
            }
        }
    }


    public function getErrors()
    {
        return $this->errors;
    }

    public function getFirstError()
    {
        return reset($this->errors);
    }


}
