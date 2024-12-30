<?php

namespace LaravelGenerator\Classes;

use Illuminate\Support\Collection;

class Table
{
    public function __construct(
        public string $tableName,

        /** @var Collection<int,Column> */
        public Collection $columns,

        /** @var Collection<int,string> */
        public Collection $fillableColumns,

        /** @var Collection<int,string> */
        public Collection $factoryColumns,

        /** @var Collection<string,array<int,string>> */
        public Collection $validationRules,

        /** @var Collection<string,array<int,string>> */
        public Collection $updateValidationRules,
    ) {
    }

    public function getName(): string
    {
        return $this->tableName;
    }

    public function getModelName(): string
    {
        return str($this->tableName)->singular()->studly()->toString();
    }

    public function getVariableName(): string
    {
        return str($this->getModelName())->camel()->toString();
    }

    public function getPluralVariableName(): string
    {
        return str($this->getModelName())->plural()->camel()->toString();
    }

    public function getControllerName(): string
    {
        return "{$this->getModelName()}Controller";
    }

    public function getRouteName(): string
    {
        return str($this->getModelName())->plural()->kebab()->toString();
    }

    public function getResourceName(): string
    {
        return "{$this->getModelName()}Resource";
    }

    public function getStoreRequestName(): string
    {
        return "{$this->getModelName()}StoreRequest";
    }

    public function getUpdateRequestName(): string
    {
        return "{$this->getModelName()}UpdateRequest";
    }

    public function getMigrationName(): string
    {
        return "create_{$this->tableName}_table";
    }

    public function getFactoryName(): string
    {
        return "{$this->getModelName()}Factory";
    }

    public function getPolicyName(): string
    {
        return "{$this->getModelName()}Policy";
    }

    public static function fromArray(array $data)
    {
    }
}
