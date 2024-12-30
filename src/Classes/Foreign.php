<?php

namespace LaravelGenerator\Classes;

class Foreign
{
    public function __construct(
        public string $references,
        public string $on,
    ) {
    }
}
