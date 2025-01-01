<?php

namespace LaravelGenerator\Classes;

class NumericColumn extends PrimaryCloumn
{
    public bool $unsigned;

    public function __construct(
        string $id,
        string $name,
        string $type,
        bool $isPrimary,
        bool $isNullable,
        bool $isForeign,
        ?bool $unsigned = false,
        ?Foreign $foreign = null
    ) {
        parent::__construct($id, $name, $type, $isPrimary, $isNullable, $isForeign, $foreign);

        $this->unsigned = $unsigned;
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
            $data['name'] ?? '',
            $data['type'] ?? '',
            $data['isPrimary'] ?? false,
            $data['isNullable'] ?? false,
            $data['isForeign'] ?? false,
            $data['unsigned'] ?? false,
            $foreign,
        );
    }
}
