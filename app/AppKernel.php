<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

require_once 'bzion-load.php';

class AppKernel extends Kernel
{
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/Resource/config_'. $this->getEnvironment() . '.yml');
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
        );

        if ($this->getEnvironment() == 'profile') {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
        }

        return $bundles;
    }

    public function boot()
    {
        parent::boot();

        Service::setGenerator($this->container->get('router')->getGenerator());
        $this->setUpTwig();
    }

    static public function guessEnvironment() {
        if (!defined('DEVELOPMENT'))
            return false;

        switch (DEVELOPMENT) {
        case 1: return "dev"; break;
        case 2: return "profile"; break;
        default: return "prod"; break;
        }
    }

    private function setUpTwig()
    {
        $cacheDir = $this->isDebug() ? null : $this->getCacheDir() . '/twig';

        // Set up the twig templating environment to parse views
        $loader = new Twig_Loader_Filesystem(__DIR__.'/../views');

        $twig = new Twig_Environment($loader, array(
            'cache' => $cacheDir,
            'debug' => $this->isDebug()
        ));

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

        if ($request->attributes->get('_defaultHandler')) {
            return parent::handle($request, $type, $catch);
        }

        $request->setSession($this->container->get('session'));

        Service::setRequest($request);
        Service::getTemplateEngine()->addGlobal("request", $request);
        Service::getTemplateEngine()->addGlobal("session", $request->getSession());
        Service::getTemplateEngine()->addGlobal("me",
                 new Player($request->getSession()->get('playerId')));

        $con = Controller::getController($request->attributes);
        $response = $con->callAction();

        $event = new FilterResponseEvent($this, $request, $type, $response);
        $this->container->get('event_dispatcher')->dispatch(KernelEvents::RESPONSE, $event);

        return $response;
    }
}
