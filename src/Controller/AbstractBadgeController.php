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

namespace App\Controller;

use App\Badge\Provider\BadgeProviderFactory;
use App\Entity\Badge;
use App\Exception\InvalidProviderException;
use App\Service\BadgeService;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * AbstractBadgeController.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
abstract class AbstractBadgeController
{
    use CacheableResponseTrait;

    protected const DEFAULT_CONTENT_TYPE = 'image/svg+xml; charset=utf-8';

    protected BadgeProviderFactory $badgeProviderFactory;
    protected BadgeService $badgeService;

    #[Required]
    public function setBadgeProviderFactory(BadgeProviderFactory $badgeProviderFactory): void
    {
        $this->badgeProviderFactory = $badgeProviderFactory;
    }

    #[Required]
    public function setBadgeService(BadgeService $badgeService): void
    {
        $this->badgeService = $badgeService;
    }

    protected function getBadgeResponse(
        Request $request,
        Badge $badge,
        string $provider = null,
        DateTime $cacheExpirationDate = null,
    ): Response {
        try {
            $providerClass = $this->badgeProviderFactory->get($provider);
        } catch (InvalidProviderException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        if ('svg' === $request->getRequestFormat()) {
            $badgeResponse = $this->badgeService->get($badge, $providerClass);
            $contentType = $badgeResponse->getHeader('Content-Type')[0] ?? self::DEFAULT_CONTENT_TYPE;

            return new Response(
                $badgeResponse->getBody(),
                Response::HTTP_OK,
                [
                    'Content-Type' => $contentType,
                ],
            );
        }

        $response = $providerClass->createResponse($badge);

        if (null !== $cacheExpirationDate) {
            $this->markResponseAsCacheable($response, $cacheExpirationDate);
        }

        return $response;
    }
}
