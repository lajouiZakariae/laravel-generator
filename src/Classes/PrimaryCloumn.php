<?php

namespace LaravelGenerator\Classes;

class PrimaryCloumn extends Column
{
    public bool $isPrimary;

    public bool $isForeign;

    public ?Foreign $foreign = null;

    public function __construct(
        string $id,
        string $name,
        string $type,
        bool $isPrimary,
        bool $isNullable,
        bool $isForeign,
        ?Foreign $foreign = null,
    ) {
        parent::__construct($id, $type, $name, $isNullable);

        $this->isPrimary = $isPrimary;

        $this->isForeign = $isForeign;

        $this->foreign = $foreign;
    }

    public static function fromArray(array $data): self
    {
        $foreign = null;

        if (!empty($data['foreign']) && is_array($data['foreign'])) {
            $foreign = new Foreign(
                $data['foreign']['references'] ?? '',
                $data['foreign']['on'] ?? '',
            );
        }

        return new self(
            $data['id'] ?? '',
            $data['name'] ?? '',
            $data['type'] ?? '',
            $data['isPrimary'] ?? false,
            $data['isNullable'] ?? false,
            $data['isForeign'] ?? false,
            $foreign,
        );
    }
}
