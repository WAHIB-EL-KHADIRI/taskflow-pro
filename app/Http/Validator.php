<?php

declare(strict_types=1);

namespace App\Http;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleSet) {
            $fieldRules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $data[$field] ?? null;
            foreach ($fieldRules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $methodName = 'rule' . ucfirst($rule);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($field, $value, $params, $data);
                }
            }
        }
        return empty($this->errors);
    }

    private function ruleRequired(string $field, mixed $value, array $params, array $data): void
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            $this->addError($field, 'Ce champ est requis.');
        }
    }

    private function ruleEmail(string $field, mixed $value, array $params, array $data): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "L'adresse email n'est pas valide.");
        }
    }

    private function ruleMin(string $field, mixed $value, array $params, array $data): void
    {
        $min = (int)($params[0] ?? 0);
        if (is_string($value) && mb_strlen($value) < $min) {
            $this->addError($field, "Ce champ doit contenir au moins {$min} caract\u00e8res.");
        }
        if (is_numeric($value) && (float)$value < $min) {
            $this->addError($field, "Ce champ doit \u00eatre sup\u00e9rieur \u00e0 {$min}.");
        }
    }

    private function ruleMax(string $field, mixed $value, array $params, array $data): void
    {
        $max = (int)($params[0] ?? 255);
        if (is_string($value) && mb_strlen($value) > $max) {
            $this->addError($field, "Ce champ ne doit pas d\u00e9passer {$max} caract\u00e8res.");
        }
        if (is_numeric($value) && (float)$value > $max) {
            $this->addError($field, "Ce champ ne doit pas d\u00e9passer {$max}.");
        }
    }

    private function ruleAlpha(string $field, mixed $value, array $params, array $data): void
    {
        if ($value !== null && $value !== '' && !preg_match('/^[\p{L}]+$/u', $value)) {
            $this->addError($field, 'Ce champ ne peut contenir que des lettres.');
        }
    }

    private function ruleNumeric(string $field, mixed $value, array $params, array $data): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, 'Ce champ doit \u00eatre num\u00e9rique.');
        }
    }

    private function ruleConfirmed(string $field, mixed $value, array $params, array $data): void
    {
        $confirmField = $field . '_confirmation';
        if (($data[$confirmField] ?? null) !== $value) {
            $this->addError($field, 'La confirmation ne correspond pas.');
        }
    }

    private function ruleUnique(string $field, mixed $value, array $params, array $data): void
    {
        if (empty($params[0]) || $value === null || $value === '') return;
        $table = $params[0];
        $column = $params[1] ?? $field;
        $excludeId = $params[2] ?? null;
        $db = \App\Infrastructure\Persistence\Database::getInstance();
        $sql = "SELECT COUNT(*) as c FROM {$table} WHERE {$column} = ?";
        $p = [$value];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $p[] = $excludeId;
        }
        $result = $db->fetch($sql, $p);
        if (($result['c'] ?? 0) > 0) {
            $this->addError($field, 'Cette valeur existe d\u00e9j\u00e0.');
        }
    }

    private function ruleUrl(string $field, mixed $value, array $params, array $data): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "L'URL n'est pas valide.");
        }
    }

    private function ruleDate(string $field, mixed $value, array $params, array $data): void
    {
        if ($value !== null && $value !== '' && !strtotime($value)) {
            $this->addError($field, 'La date est invalide.');
        }
    }

    private function ruleIn(string $field, mixed $value, array $params, array $data): void
    {
        if ($value !== null && !in_array((string)$value, $params, true)) {
            $this->addError($field, 'Valeur non autoris\u00e9e.');
        }
    }

    private function ruleRegex(string $field, mixed $value, array $params, array $data): void
    {
        $pattern = $params[0] ?? '/^.*$/';
        if ($value !== null && $value !== '' && preg_match($pattern, (string)$value) !== 1) {
            $this->addError($field, 'Format invalide.');
        }
    }

    private function ruleInteger(string $field, mixed $value, array $params, array $data): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'Doit \u00eatre un nombre entier.');
        }
    }

    private function ruleExists(string $field, mixed $value, array $params, array $data): void
    {
        if (empty($params[0]) || $value === null || $value === '') return;
        $table = $params[0];
        $column = $params[1] ?? 'id';
        $db = \App\Infrastructure\Persistence\Database::getInstance();
        $r = $db->fetch("SELECT COUNT(*) as c FROM {$table} WHERE {$column} = ?", [$value]);
        if (($r['c'] ?? 0) === 0) {
            $this->addError($field, 'R\u00e9f\u00e9rence introuvable.');
        }
    }

    private function ruleFile(string $field, mixed $value, array $params, array $data): void
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return;
        if (empty($params[0])) return;
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $allowed = explode(',', $params[0]);
        if (!in_array($ext, $allowed, true)) {
            $this->addError($field, 'Format de fichier non autoris\u00e9. Formats: ' . $params[0]);
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        $first = reset($this->errors);
        return $first ? $first[0] : null;
    }

    public function failed(): bool
    {
        return !empty($this->errors);
    }

    public function add(string $field, string $message): void
    {
        $this->addError($field, $message);
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
}
