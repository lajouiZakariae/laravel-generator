<?php

namespace LaravelGenerator\Classes;

class NumericColumn extends Column
{
    public bool $unsigned;

    public ?string $maxValue = null;

    public function __construct(
        string $id,
        string $name,
        string $type,
        bool $isPrimary,
        bool $isNullable,
        bool $isForeign,
        ?string $maxValue = null,
        ?bool $unsigned = false,
        ?Foreign $foreign = null
    ) {
        parent::__construct($id, $name, $type, $isPrimary, $isNullable, $isForeign, $foreign);

        $this->unsigned = $unsigned;

        $this->maxValue = $maxValue;
    }

    public static function fromArray(array $data): self
    {
        $foreign = null;

        if (!empty($data['foreign']) && is_array($data['foreign'])) {
            $foreign = new Foreign(
                $data['foreign']['references'] ?? '',
                $data['foreign']['on'] ?? ''
            );
        }

        return new self(
            $data['id'] ?? '',
            $data['type'] ?? '',
            $data['name'] ?? '',
            $data['isPrimary'] ?? false,
            $data['isNullable'] ?? false,
            $data['isForeign'] ?? false,
            $data['maxValue'] ?? null,
            $data['unsigned'] ?? false,
            $foreign,
        );
    }
}
