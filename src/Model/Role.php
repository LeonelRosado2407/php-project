<?php

declare(strict_types=1);

namespace Keel\Model;

/**
 * Class Role
 * @package Keel\Model
 */
class Role
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    )
    {}
}
