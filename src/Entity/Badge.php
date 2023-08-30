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

namespace App\Entity;

use App\Enums\Color;
use App\Value\NumberFormatter;

use function implode;

/**
 * Badge.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class Badge
{
    private const COLOR_MAP = [
        'extension' => Color::Orange,
        'version' => Color::Orange,
        'downloads' => Color::Blue,
        'typo3' => Color::Orange,
        'error' => Color::Red,
        'stability_stable' => Color::Green,
        'stability_beta' => Color::Yellow,
        'stability_alpha' => Color::Red,
        'stability_experimental' => Color::Red,
        'stability_test' => Color::Gray,
        'stability_obsolete' => Color::Gray,
        'stability_excludeFromUpdates' => Color::Gray,
        'verified' => Color::Green,
        'not_verified' => Color::Gray,
    ];

    /**
     * @param non-empty-string $label
     * @param non-empty-string $message
     */
    public function __construct(
        private string $label,
        private string $message,
        private Color $color,
        private bool $isError = false,
    ) {
    }

    public static function static(): self
    {
        return new self(
            'typo3',
            'inspiring people to share',
            Color::Orange,
        );
    }

    /**
     * @param non-empty-string $extension
     */
    public static function forExtension(string $extension): self
    {
        return new self(
            'extension',
            $extension,
            self::COLOR_MAP['extension'],
        );
    }

    /**
     * @param non-empty-string $version
     */
    public static function forVersion(string $version): self
    {
        return new self(
            'version',
            $version,
            self::COLOR_MAP['version'],
        );
    }

    public static function forDownloads(int $downloads): self
    {
        return new self(
            'downloads',
            strtolower(NumberFormatter::format($downloads)),
            self::COLOR_MAP['downloads'],
        );
    }

    /**
     * @param list<positive-int> $typo3Versions
     */
    public static function forTypo3Versions(array $typo3Versions): self
    {
        if ([] === $typo3Versions) {
            return self::forError();
        }

        sort($typo3Versions);

        $lastValue = array_pop($typo3Versions);
        $message = implode(' & ', array_filter([
            implode(', ', $typo3Versions),
            $lastValue,
        ]));

        return new self(
            'typo3',
            $message,
            self::COLOR_MAP['typo3'],
        );
    }

    /**
     * @param non-empty-string $stability
     */
    public static function forStability(string $stability): self
    {
        return new self(
            'stability',
            $stability,
            self::COLOR_MAP['stability_'.$stability] ?? Color::Orange,
        );
    }

    public static function forVerification(bool $verified): self
    {
        return new self(
            'typo3',
            $verified ? 'verified' : 'not verified',
            $verified ? self::COLOR_MAP['verified'] : self::COLOR_MAP['not_verified'],
        );
    }

    public static function forError(): self
    {
        return new self(
            'typo3',
            'error',
            self::COLOR_MAP['error'],
            true,
        );
    }

    /**
     * @return non-empty-string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return non-empty-string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    public function isError(): bool
    {
        return $this->isError;
    }
}
