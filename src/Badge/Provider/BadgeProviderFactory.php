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

namespace App\Badge\Provider;

use App\Exception\InvalidProviderException;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * BadgeProviderFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class BadgeProviderFactory
{
    /**
     * @param ServiceLocator<BadgeProvider> $providers
     */
    public function __construct(
        private ServiceLocator $providers,
    ) {
    }

    /**
     * @throws InvalidProviderException
     */
    public function get(string $name = null): BadgeProvider
    {
        if (null === $name) {
            return $this->getDefaultProvider();
        }

        /** @var class-string<BadgeProvider> $service */
        foreach ($this->providers->getProvidedServices() as $serviceId => $service) {
            if ($service::getIdentifier() === $name) {
                return $this->providers->get($serviceId);
            }
        }

        throw InvalidProviderException::create($name);
    }

    /**
     * @return array<string, BadgeProvider>
     */
    public function getAll(): array
    {
        $providers = [];

        foreach (array_keys($this->providers->getProvidedServices()) as $serviceId) {
            $provider = $this->providers->get($serviceId);
            $providers[$provider::getIdentifier()] = $provider;
        }

        return $providers;
    }

    private function getDefaultProvider(): BadgeProvider
    {
        return $this->providers->get(ShieldsBadgeProvider::getIdentifier());
    }
}
