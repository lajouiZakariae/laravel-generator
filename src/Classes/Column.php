<?php

namespace LaravelGenerator\Classes;

class Column
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public bool $isPrimary,
        public bool $isNullable,
        public bool $isForeign,
        public ?Foreign $foreign = null
    ) {
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
            $foreign
        );
    }
}
