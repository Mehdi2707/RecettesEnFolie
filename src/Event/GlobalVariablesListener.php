<?php

namespace App\Event;

use App\Service\CategoriesService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class GlobalVariablesListener implements EventSubscriberInterface
{
    private $twig;
    private $categoryService;

    public function __construct(Environment $twig, CategoriesService $categoryService)
    {
        $this->twig = $twig;
        $this->categoryService = $categoryService;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $categories = $this->categoryService->getAllCategories();
        $this->twig->addGlobal('categories', $categories);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}