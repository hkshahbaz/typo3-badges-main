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

use App\Badge\Provider\BadgeProvider;
use App\Entity\Badge;
use App\Entity\Dto\BadgeResponse;
use DateTime;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * BadgeService.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final readonly class BadgeService
{
    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
        // 1 day
        private int $cacheExpirationPeriod = 60 * 60 * 24,
    ) {
    }

    public function get(Badge $badge, BadgeProvider $provider): BadgeResponse
    {
        $url = $provider->generateUriForBadge($badge);

        // Fetch extension metadata from cache or external API
        $badgeResponse = $this->cache->get(
            $this->calculateCacheIdentifier(['url' => $url]),
            fn (ItemInterface $item) => $this->sendRequestAndCacheResponse($url, $item),
            null,
            $cacheMetadata,
        );

        return new BadgeResponse(
            $badgeResponse['body'],
            $badgeResponse['headers'],
            $this->determineCacheExpiryDateFromCacheMetadata($cacheMetadata),
        );
    }

    /**
     * @return array{body: string, headers: string[][]}
     */
    private function sendRequestAndCacheResponse(string $url, ItemInterface $item): array
    {
        $response = $this->client->request('GET', $url);
        $responseData = [
            'body' => $response->getContent(),
            'headers' => $response->getHeaders(),
        ];

        // Store only lowercase header names
        foreach ($responseData['headers'] as $name => $values) {
            $headerLowercase = strtolower($name);

            if ($headerLowercase === $name) {
                continue;
            }

            $responseData['headers'][$headerLowercase] = $values;

            unset($responseData['headers'][$name]);
        }

        $item->expiresAfter($this->cacheExpirationPeriod);
        $item->set($responseData);

        return $responseData;
    }

    /**
     * @param array<string, string> $options
     */
    private function calculateCacheIdentifier(array $options = []): string
    {
        return hash('sha512', 'badge_response_'.json_encode($options, JSON_THROW_ON_ERROR));
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
