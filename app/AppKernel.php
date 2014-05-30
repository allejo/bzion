<?php

use BZIon\Twig\PluralFunction;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;
use Symfony\Component\Form\Extension\DataCollector\DataCollectorExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

require_once __DIR__ . '/../bzion-load.php';

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
        Service::setEnvironment($this->getEnvironment());
        $this->setUpTwig();
    }

    public static function guessEnvironment()
    {
        if (!defined('DEVELOPMENT'))
            return false;

        switch (DEVELOPMENT) {
        case 1: return "dev";
        case 2: return "profile";
        default: return "prod";
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
        $formEngine = new TwigRendererEngine(array('form_layout.html.twig'));
        $formEngine->setEnvironment($twig);
        $twig->addExtension(new RoutingExtension(Service::getGenerator()));
        $twig->addExtension(
            new FormExtension(new TwigRenderer($formEngine))
        );
        $twig->addFunction(PluralFunction::get());
        if ($this->isDebug())
            $twig->addExtension(new Twig_Extension_Debug());

        Service::setTemplateEngine($twig);
    }

    private function setUpFormFactory(&$session)
    {
        $csrfProvider = new SessionCsrfProvider($session, "secret");
        $validator = Validation::createValidator();

        $formFactoryBuilder = Forms::createFormFactoryBuilder()
                       ->addExtension(new HttpFoundationExtension())
                       ->addExtension(new ValidatorExtension($validator))
                       ->addExtension(new CsrfExtension($csrfProvider));

        // Make sure that the profiler shows information about the forms
        $formDataCollector = $this->container->get('data_collector.form', null);
        if ($formDataCollector) {
            $formFactoryBuilder->addExtension(new DataCollectorExtension($formDataCollector));
        }

        Service::setFormFactory($formFactoryBuilder->getFormFactory());
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

        $session = $this->container->get('session');
        $request->setSession($session);

        $this->setUpFormFactory($session);

        Service::setRequest($request);

        $con = Controller::getController($request->attributes);
        $response = $con->callAction();

        $event = new FilterResponseEvent($this, $request, $type, $response);
        $this->container->get('event_dispatcher')->dispatch(KernelEvents::RESPONSE, $event);

        return $response;
    }
}
