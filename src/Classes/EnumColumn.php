<?php

namespace LaravelGenerator\Classes;

class EnumColumn extends Column
{

    public function __construct(
        public string $id,
        public string $name,
        public bool $isNullable,
        public array $enumValues,
    ) {
        parent::__construct($id, 'enum', $name, $isNullable);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? '',
            $data['name'] ?? '',
            $data['type'] ?? '',
            $data['enumValues'],
        );
    }
}
