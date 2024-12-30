<?php

namespace LaravelGenerator\Classes;

class StringColumn extends Column
{
    public string $stringMax;

    public function __construct(
        string $id,
        string $name,
        bool $isPrimary,
        bool $isNullable,
        bool $isForeign,
        string $stringMax,
        ?Foreign $foreign = null
    ) {
        parent::__construct($id, $name, 'string', $isPrimary, $isNullable, $isForeign, $foreign);

        $this->stringMax = $stringMax;
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
            $data['isPrimary'] ?? false,
            $data['isNullable'] ?? false,
            $data['isForeign'] ?? false,
            $data['stringMax'] ?? '',
            $foreign
        );
    }
}
