<?php

namespace LaravelGenerator\Classes;

class EnumColumn
{
    public string $type = "enum";

    public function __construct(
        public string $id,
        public string $name,
        public bool $isNullable,
        public array $possibleValues,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? '',
            $data['name'] ?? '',
            $data['type'] ?? '',
            $data['possibleValues'],
        );
    }
}
