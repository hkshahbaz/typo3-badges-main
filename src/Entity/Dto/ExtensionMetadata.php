<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony project "eliashaeussler/typo3-badges".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Entity\Dto;

use ArrayAccess;
use DateTime;

/**
 * ExtensionMetadata.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @implements \ArrayAccess<int|string, mixed>
 */
final class ExtensionMetadata implements ArrayAccess
{
    public function __construct(
        /**
         * @var array<int|string, mixed>
         */
        private array $metadata,
        private readonly ?DateTime $expiryDate = null,
    ) {
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getExpiryDate(): ?DateTime
    {
        return $this->expiryDate;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->metadata[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->metadata[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->metadata[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->metadata[$offset]);
    }
}
