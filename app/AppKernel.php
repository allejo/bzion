<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AppKernel extends Kernel
{
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config.yml');
    }

    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
        );
    }

    public function boot()
    {
        parent::boot();

        Service::setGenerator($this->container->get('router')->getGenerator());
        $this->setUpTwig();
    }

    private function setUpTwig()
    {
        $cacheDir = $this->isDebug() ? null : $this->getCacheDir() . '/twig';

        // Set up the twig templating environment to parse views
        $loader = new Twig_Loader_Filesystem(__DIR__.'/../views');

        $this->container->set('twig.loader', $loader);

        $twig = $this->container->get('twig');
        if ($this->isDebug())
            $twig->setCache(false);

        // Load the routing extension to twig, which adds functions such as path()
        $twig->addExtension(new RoutingExtension(Service::getGenerator()));
        if ($this->isDebug())
            $twig->addExtension(new Twig_Extension_Debug());

        $twig->addGlobal("pages", Page::getPages());

        Service::setTemplateEngine($twig);

    }

    public function handle(Request $request, $type=1, $catch=true)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        $event = new GetResponseEvent($this, $request, $type);
        $this->container->get('event_dispatcher')->dispatch(KernelEvents::REQUEST, $event);

        $request->setSession(Service::getNewSession());

        Service::setRequest($request);
        Service::getTemplateEngine()->addGlobal("request", $request);
        Service::getTemplateEngine()->addGlobal("session", $request->getSession());
        Service::getTemplateEngine()->addGlobal("me",
                 new Player($request->getSession()->get('playerId')));

        $con = Controller::getController($request->attributes);
        $response = $con->callAction();

        return $response;
    }
}
