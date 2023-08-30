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

use App\Entity\Badge;
use App\Enums\Color;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

use function array_map;

/**
 * ShieldsBadgeProvider.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ShieldsBadgeProvider implements BadgeProvider
{
    use RoutingTrait;

    public const IDENTIFIER = 'shields';

    private const BADGE_URL_PATTERN = 'https://img.shields.io/static/v1?label={label}&message={message}&color={color}&logo={logo}';
    private const ENDPOINT_URL_PATTERN = 'https://shields.io/endpoint?url={url}';

    public function __construct(
        RouterInterface $router,
    ) {
        $this->router = $router;
    }

    public static function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public static function getName(): string
    {
        return 'Shields.io';
    }

    public function createResponse(Badge $badge): Response
    {
        return new JsonResponse([
            'schemaVersion' => 1,
            'label' => $badge->getLabel(),
            'message' => $badge->getMessage(),
            'color' => $this->getColorValue($badge->getColor()),
            'isError' => $badge->isError(),
            'namedLogo' => 'typo3',
        ]);
    }

    public function generateUriForRoute(Route|string $route, array $routeParameters = []): string
    {
        // Enforce provider parameter
        $routeParameters['provider'] = self::IDENTIFIER;

        if ($route instanceof Route) {
            $routeName = $this->identifyRoute($route);
        } else {
            $routeName = $route;
        }

        $appUrl = $this->router->generate($routeName, $routeParameters, UrlGeneratorInterface::ABSOLUTE_URL);

        return strtr(self::ENDPOINT_URL_PATTERN, [
            '{url}' => $appUrl,
        ]);
    }

    public function generateUriForBadge(Badge $badge): string
    {
        $urlParameters = [
            '{label}' => $badge->getLabel(),
            '{message}' => $badge->getMessage(),
            '{color}' => $this->getColorValue($badge->getColor()),
            '{logo}' => 'typo3',
        ];

        return strtr(self::BADGE_URL_PATTERN, array_map('rawurlencode', $urlParameters));
    }

    /**
     * @codeCoverageIgnore
     */
    public function getUrlPattern(): string
    {
        return self::ENDPOINT_URL_PATTERN;
    }

    public function getProviderUrl(): string
    {
        return 'https://shields.io';
    }

    private function getColorValue(Color $color): string
    {
        return match ($color) {
            Color::Blue => 'blue',
            Color::Gray => 'lightgrey',
            Color::Green => 'green',
            Color::Orange => 'orange',
            Color::Red => 'red',
            Color::Yellow => 'yellow',
        };
    }
}
