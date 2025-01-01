<?php

namespace LaravelGenerator\Classes;

class BoolColumn extends Column
{
    public function __construct(
        public string $id,
        public string $name,
        public bool $isNullable,
    ) {
        parent::__construct($id, 'bool', $name, $isNullable);
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
