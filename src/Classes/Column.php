<?php

namespace LaravelGenerator\Classes;

class Column
{

    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public bool $isNullable,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? '',
            $data['type'] ?? '',
            $data['name'] ?? '',
            $data['isNullable'] ?? false,
        );
    }
}
