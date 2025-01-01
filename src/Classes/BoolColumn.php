<?php

namespace LaravelGenerator\Classes;

class BoolColumn
{
    public string $type = "bool";

    public function __construct(
        public string $id,
        public string $name,
        public bool $isNullable,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? '',
            $data['name'] ?? '',
            $data['type'] ?? '',
        );
    }
}
