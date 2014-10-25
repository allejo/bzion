<?php

use BZIon\Cache\ModelCache;
use BZIon\Session\DatabaseSessionHandler;
use BZIon\Twig\LinkToFunction;
use BZIon\Twig\PluralFilter;
use BZIon\Twig\YesNoFilter;
use BZIon\Twig\ValidTest;
use BZIon\Twig\InvalidTest;
use Liip\ImagineBundle\Templating\ImagineExtension;
use RaulFraile\Bundle\LadybugBundle\Twig\Extension\LadybugExtension;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bundle\TwigBundle\Extension\AssetsExtension;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
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
        $loader->load(__DIR__.'/Resource/symfony_'. $this->getEnvironment() . '.yml');
    }

    public function registerBundles()
    {
        $bundles = array(
            new BZIon\Config\ConfigBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
        );

        if ($this->getEnvironment() == 'profile') {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new RaulFraile\Bundle\LadybugBundle\RaulFraileLadybugBundle();
        }

        return $bundles;
    }

    public function boot()
    {
        parent::boot();

        if (!$this->container->getParameter('bzion.miscellaneous.development')) {
            if ($this->getEnvironment() != 'prod' || $this->isDebug()) {
                throw new ForbiddenDeveloperAccessException(
                    'You are not allowed to access this page in a non-production '.
                    'environment. Please change the "development" configuration '.
                    'value and clear the cache before proceeding.'
                );
            }
        }

        if ($this->getEnvironment() == 'profile') {
            Debug::enable();
        }

        Service::setGenerator($this->container->get('router')->getGenerator());
        Service::setEnvironment($this->getEnvironment());
        Service::setModelCache(new ModelCache());
        Service::setContainer($this->container);
        $this->setUpTwig();

        // Ratchet doesn't support PHP's native session storage, so use our own
        // if we need it
        if (Service::getParameter('bzion.notifications.websocket.enabled') &&
            $this->getEnvironment() !== 'test') {
            $storage = new NativeSessionStorage(array(), new DatabaseSessionHandler());
            $session = new Session($storage);
            Service::getContainer()->set('session', $session);
        }

        Notification::initializeAdapters();
    }

    /**
     * Find out whether the `dev` or the `profile` environment should be used
     * for development, depending on the existance of the profiler bundle
     *
     * @return string The suggested kernel environment
     */
    public static function guessDevEnvironment()
    {
        // If there is a profiler, use the environment with the profiler
        if (class_exists('Symfony\Bundle\WebProfilerBundle\WebProfilerBundle')) {
            return 'profile';
        }

        return 'dev';
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
        $twig->addExtension(
            new ImagineExtension($this->container->get('liip_imagine.cache.manager'))
        );
        $twig->addExtension(
            new AssetsExtension($this->container, $this->container->get('router')->getContext())
        );

        if ($this->getEnvironment() == 'profile') {
            $twig->addExtension(new LadybugExtension($this->container->get('ladybug.dumper')));
        }

        $twig->addFunction(LinkToFunction::get());
        $twig->addFilter(PluralFilter::get());
        $twig->addFilter(YesNoFilter::get());
        $twig->addTest(ValidTest::get());
        $twig->addTest(InvalidTest::get());
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

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');

        $event = new GetResponseEvent($this, $request, $type);
        $this->container->get('event_dispatcher')->dispatch(KernelEvents::REQUEST, $event);

        if ($request->attributes->get('_defaultHandler')) {
            return parent::handle($request, $type, $catch);
        }

        $session = $this->container->get('session');
        $session->start();
        $this->setUpFormFactory($session);

        Service::setRequest($request);

        $con = Controller::getController($request->attributes);
        $response = $con->callAction();

        $event = new FilterResponseEvent($this, $request, $type, $response);
        $this->container->get('event_dispatcher')->dispatch(KernelEvents::RESPONSE, $event);

        // Do not lose the session data when the client's browser redirects too
        // fast, without allowing the session handler to store the session
        if ($type == Kernel::MASTER_REQUEST) {
            if ($session->isStarted()) {
                $session->save();
            }
        }

        return $response;
    }
}
