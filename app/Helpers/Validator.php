<?php

namespace App\Helpers;

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules): self
    {
        $v = new self($data);
        $v->validate($rules);
        return $v;
    }

    public function validate(array $rules): void
    {
        foreach ($rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            $ruleList = explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

        match ($ruleName) {
            'required' => $this->checkRequired($field, $value),
            'email'    => $this->checkEmail($field, $value),
            'min'      => $this->checkMin($field, $value, (int) $param),
            'max'      => $this->checkMax($field, $value, (int) $param),
            'numeric'  => $this->checkNumeric($field, $value),
            'integer'  => $this->checkInteger($field, $value),
            'date'     => $this->checkDate($field, $value),
            'in'       => $this->checkIn($field, $value, explode(',', $param)),
            'cpf'      => $this->checkCpf($field, $value),
            'confirmed'=> $this->checkConfirmed($field, $value, $param ?? $field . '_confirmation'),
            default    => null,
        };
    }

    private function checkRequired(string $field, mixed $value): void
    {
        if (empty($value) && $value !== '0') {
            $this->addError($field, 'O campo é obrigatório.');
        }
    }

    private function checkEmail(string $field, mixed $value): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'Informe um e-mail válido.');
        }
    }

    private function checkMin(string $field, mixed $value, int $min): void
    {
        if (!empty($value) && strlen((string) $value) < $min) {
            $this->addError($field, "Mínimo de {$min} caracteres.");
        }
    }

    private function checkMax(string $field, mixed $value, int $max): void
    {
        if (!empty($value) && strlen((string) $value) > $max) {
            $this->addError($field, "Máximo de {$max} caracteres.");
        }
    }

    private function checkNumeric(string $field, mixed $value): void
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, 'Informe um número válido.');
        }
    }

    private function checkInteger(string $field, mixed $value): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'Informe um número inteiro válido.');
        }
    }

    private function checkDate(string $field, mixed $value): void
    {
        if (!empty($value)) {
            $d = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$d || $d->format('Y-m-d') !== $value) {
                $this->addError($field, 'Informe uma data válida (AAAA-MM-DD).');
            }
        }
    }

    private function checkIn(string $field, mixed $value, array $allowed): void
    {
        if (!empty($value) && !in_array($value, $allowed)) {
            $this->addError($field, 'Valor inválido para este campo.');
        }
    }

    private function checkCpf(string $field, mixed $value): void
    {
        if (empty($value)) return;
        $cpf = preg_replace('/[^0-9]/', '', $value);
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            $this->addError($field, 'CPF inválido.');
            return;
        }
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += $cpf[$i] * ($t + 1 - $i);
            }
            $remainder = (10 * $sum) % 11;
            if ($cpf[$t] != ($remainder < 10 ? $remainder : 0)) {
                $this->addError($field, 'CPF inválido.');
                return;
            }
        }
    }

    private function checkConfirmed(string $field, mixed $value, string $confirmField): void
    {
        if (!empty($value) && $value !== ($this->data[$confirmField] ?? null)) {
            $this->addError($field, 'Os campos não coincidem.');
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function sanitized(): array
    {
        return array_map(fn($v) => is_string($v) ? trim($v) : $v, $this->data);
    }
}
