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
        public ?Collection $fillableColumns = null,

        /** @var Collection<string,string> */
        public ?Collection $factoryColumns = null,

        /** @var Collection<string,array<int,string>> */
        public ?Collection $validationRules = null,

        /** @var Collection<string,array<int,string>> */
        public ?Collection $updateValidationRules = null,

        /** @var Collection<int,Relation> */
        public ?Collection $relations = null,
    ) {
    }

    /** 
     * @param Collection<int,string>
     */
    public function setFillableColumns(Collection $fillableColumns): void
    {
        $this->fillableColumns = $fillableColumns;
    }

    /** 
     * @param Collection<string,string>
     */
    public function setFactoryColumns(Collection $factoryColumns): void
    {
        $this->factoryColumns = $factoryColumns;
    }

    /** 
     * @param Collection<string,array<int,string>>
     */
    public function setValidationRules(Collection $validationRules): void
    {
        $this->validationRules = $validationRules;
    }

    /** 
     * @param Collection<string,array<int,string>>
     */
    public function setUpdateValidationRules(Collection $updateValidationRules): void
    {
        $this->updateValidationRules = $updateValidationRules;
    }

    /** 
     * @param Collection<string,array<int,string>>
     */
    public function setRelations(Collection $relations): void
    {
        $this->relations = $relations;
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

    public static function generateEnumName(string $tableName, string $columnName): string
    {
        $tableModelName = str($tableName)->singular()->camel()->ucfirst()->toString();
        $enumColumnCamelCaseName = str($columnName)->camel()->ucfirst()->toString();

        if (!str("$tableModelName$enumColumnCamelCaseName")->endsWith('Enum')) {
            return "{$tableModelName}{$enumColumnCamelCaseName}Enum";
        }

        return "$tableModelName$enumColumnCamelCaseName";
    }
}
