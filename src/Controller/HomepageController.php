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
use App\Badge\Provider\ShieldsBadgeProvider;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * HomepageController.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Route(path: '/')]
final class HomepageController extends AbstractController
{
    use CacheableResponseTrait;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly BadgeProviderFactory $badgeProviderFactory,
        private readonly ApiService $apiService,
    ) {
    }

    public function __invoke(): Response
    {
        $providers = $this->badgeProviderFactory->getAll();
        $randomExtensionMetadata = $this->apiService->getRandomExtensionMetadata();

        return $this->render('homepage.html.twig', [
            'routes' => $this->getRoutes(),
            'providers' => $providers,
            'defaultProvider' => $providers[ShieldsBadgeProvider::getIdentifier()],
            'randomExtensionKey' => $randomExtensionMetadata['key'],
        ]);
    }

    /**
     * @return array<string, \Symfony\Component\Routing\Route>
     */
    private function getRoutes(): array
    {
        return array_filter(
            $this->router->getRouteCollection()->all(),
            static fn (\Symfony\Component\Routing\Route $route) => str_starts_with($route->getPath(), '/badge/'),
        );
    }
}
