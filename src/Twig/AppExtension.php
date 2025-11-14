<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends AbstractExtension
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_current_route', [$this, 'isCurrentRoute']),
        ];
    }

    /**
     * Vérifie si la route actuelle correspond à une route
     * ou à une liste de routes.
     */
    public function isCurrentRoute(string|array $routes): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        $currentRoute = $request->attributes->get('_route');

        // Si on fournit un seul nom sous forme de string
        if (is_string($routes)) {
            return $currentRoute === $routes;
        }

        // Si on fournit un tableau de routes
        if (is_array($routes)) {
            return in_array($currentRoute, $routes, true);
        }

        return false;
    }
}
