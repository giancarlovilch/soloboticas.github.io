<?php

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(array $fields): self
    {
        foreach ($fields as $field) {
            $value = $this->data[$field] ?? null;

            if ($value === null || $value === '') {
                $this->addError($field, "El campo {$field} es obligatorio.");
            }
        }

        return $this;
    }

    public function email(string $field): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "El campo {$field} debe ser un correo válido.");
        }

        return $this;
    }

    public function min(string $field, int $length): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && mb_strlen((string) $value) < $length) {
            $this->addError($field, "El campo {$field} debe tener al menos {$length} caracteres.");
        }

        return $this;
    }

    public function max(string $field, int $length): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && mb_strlen((string) $value) > $length) {
            $this->addError($field, "El campo {$field} no debe superar los {$length} caracteres.");
        }

        return $this;
    }

    public function numeric(string $field): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, "El campo {$field} debe ser numérico.");
        }

        return $this;
    }

    public function date(string $field): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && $value !== '') {
            $parsed = date_create($value);

            if (!$parsed) {
                $this->addError($field, "El campo {$field} debe ser una fecha válida.");
            }
        }

        return $this;
    }

    public function same(string $field, string $otherField): self
    {
        $value = $this->data[$field] ?? null;
        $otherValue = $this->data[$otherField] ?? null;

        if ($value !== $otherValue) {
            $this->addError($field, "El campo {$field} debe coincidir con {$otherField}.");
        }

        return $this;
    }

    public function in(string $field, array $allowedValues): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && $value !== '' && !in_array($value, $allowedValues, true)) {
            $this->addError($field, "El campo {$field} contiene un valor no permitido.");
        }

        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
    public function exactLength(string $field, int $length): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && mb_strlen((string) $value) !== $length) {
            $this->addError($field, "El campo {$field} debe tener exactamente {$length} caracteres.");
        }

        return $this;
    }

    public function integer(string $field): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "El campo {$field} debe ser un número entero.");
        }

        return $this;
    }
}
