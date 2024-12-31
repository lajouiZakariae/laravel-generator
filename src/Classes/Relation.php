<?php

namespace LaravelGenerator\Classes;

class Relation
{
    public function __construct(
        public string $foreignKey,
        public string $foreignTable,
        public string $localKey,
        public string $localTable,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data["foreignKey"] ?? "",
            $data["foreignTable"] ?? "",
            $data["localKey"] ?? "",
            $data["localTable"] ?? "",
        );
    }

    public function getMethodName(): string
    {
        return str($this->foreignTable)->singular()->camel()->toString();
    }

    public function getModelName(): string
    {
        return str($this->foreignTable)->singular()->camel()->ucfirst()->toString();
    }
}
