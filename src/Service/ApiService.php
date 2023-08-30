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

namespace App\Service;

use App\Entity\Dto\ExtensionMetadata;
use DateTime;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * ApiService.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class ApiService
{
    private const FALLBACK_EXTENSION_KEY = 'handlebars';

    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
        private int $cacheExpirationPeriod = 3600,
    ) {
    }

    public function getExtensionMetadata(string $extensionKey): ExtensionMetadata
    {
        $apiPath = $this->buildApiPath('/extension/{extension}', ['extension' => $extensionKey]);

        // Fetch extension metadata from cache or external API
        $extensionMetadata = $this->cache->get(
            $this->calculateCacheIdentifier('typo3_api.extension_metadata', ['apiPath' => $apiPath]),
            fn (ItemInterface $item) => $this->sendRequestAndCacheResponse($apiPath, $item),
            null,
            $cacheMetadata,
        );

        return new ExtensionMetadata(
            $extensionMetadata,
            $this->determineCacheExpiryDateFromCacheMetadata($cacheMetadata),
        );
    }

    public function getRandomExtensionMetadata(): ExtensionMetadata
    {
        $apiPath = $this->buildApiPath('/extension');

        // Fetch current extensions from cache or external API
        $result = $this->cache->get(
            $this->calculateCacheIdentifier('typo3_api.random_extensions', ['apiPath' => $apiPath]),
            function (ItemInterface $item) use ($apiPath): array {
                // Build random filter options
                $filterOptions = [
                    'page' => random_int(1, 10),
                    'per_page' => 20,
                    'filter' => [
                        'typo3_version' => random_int(10, 11),
                    ],
                ];
                $apiUrl = $apiPath.'?'.http_build_query($filterOptions);

                return $this->sendRequestAndCacheResponse($apiUrl, $item, 60 * 60 * 24);
            },
            null,
            $cacheMetadata,
        );

        $extensions = $result['extensions'] ?? [];

        if ([] === $extensions) {
            return new ExtensionMetadata(['key' => self::FALLBACK_EXTENSION_KEY]);
        }

        return new ExtensionMetadata(
            ['key' => $extensions[array_rand($extensions)]['key']],
            $this->determineCacheExpiryDateFromCacheMetadata($cacheMetadata),
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    private function sendRequestAndCacheResponse(string $path, ItemInterface $item, int $expiresAfter = null): array
    {
        $response = $this->client->request('GET', $path);
        $responseArray = $response->toArray();

        $item->expiresAfter($expiresAfter ?? $this->cacheExpirationPeriod);
        $item->set($responseArray);

        return $responseArray;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function buildApiPath(string $endpoint, array $parameters = []): string
    {
        $replacePairs = array_combine(
            array_map(fn (string $parameter): string => '{'.trim($parameter, '{}').'}', array_keys($parameters)),
            array_values($parameters),
        );

        return '/api/v1/'.ltrim(strtr($endpoint, $replacePairs), '/');
    }

    /**
     * @param array<string, string> $options
     */
    private function calculateCacheIdentifier(string $key, array $options = []): string
    {
        return hash('sha512', $key.'_'.json_encode($options, JSON_THROW_ON_ERROR));
    }

    /**
     * @param array{expiry?: int|numeric-string}|null $cacheMetadata
     */
    private function determineCacheExpiryDateFromCacheMetadata(?array $cacheMetadata): ?DateTime
    {
        if (!isset($cacheMetadata[ItemInterface::METADATA_EXPIRY])) {
            return null;
        }

        $expiryDate = DateTime::createFromFormat('U', (string) (int) $cacheMetadata[ItemInterface::METADATA_EXPIRY]);

        if (false === $expiryDate) {
            return null;
        }

        return $expiryDate;
    }
}
