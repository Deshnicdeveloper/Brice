<?php

namespace App\Helpers;

class ValidationHelper {
    private $errors = [];
    private $data = [];

    public function validate($data, $rules) {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $rule) {
        $value = $this->data[$field] ?? null;

        if (is_array($rule)) {
            $ruleName = $rule[0];
            $params = array_slice($rule, 1);
        } else {
            $ruleName = $rule;
            $params = [];
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, 'This field is required');
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Invalid email format');
                }
                break;

            case 'phone':
                if (!empty($value) && !preg_match('/^[0-9+\-\s()]{6,20}$/', $value)) {
                    $this->addError($field, 'Invalid phone number format');
                }
                break;

            case 'min':
                if (strlen($value) < $params[0]) {
                    $this->addError($field, "Must be at least {$params[0]} characters");
                }
                break;

            case 'max':
                if (strlen($value) > $params[0]) {
                    $this->addError($field, "Must not exceed {$params[0]} characters");
                }
                break;

            case 'in':
                if (!in_array($value, $params[0])) {
                    $this->addError($field, 'Invalid selection');
                }
                break;

            case 'unique':
                [$table, $column, $except] = $params;
                if (!$this->isUnique($value, $table, $column, $except)) {
                    $this->addError($field, 'This value is already taken');
                }
                break;
        }
    }

    private function isUnique($value, $table, $column, $except = null) {
        $db = \App\Config\Database::getInstance()->getConnection();
        
        $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = ?";
        $params = [$value];

        if ($except) {
            $sql .= " AND id != ?";
            $params[] = $except;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'] === 0;
    }

    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError() {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return reset($fieldErrors);
            }
        }
        return null;
    }
} 