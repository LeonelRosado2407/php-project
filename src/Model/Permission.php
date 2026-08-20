<?php

declare(strict_types=1);

namespace Keel\Model;

/**
 * Class Permission
 * @package Keel\Model
 */
class Permission
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    )
    {}
}
