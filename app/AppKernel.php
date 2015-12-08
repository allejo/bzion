<?php

use BZIon\Cache\ModelCache;
use BZIon\Session\DatabaseSessionHandler;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;

require_once __DIR__ . '/../bzion-load.php';

class AppKernel extends Kernel
{
    private $request = null;

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/Resource/symfony_' . $this->getEnvironment() . '.yml');
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
            new FOS\ElasticaBundle\FOSElasticaBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
        );

        if ($this->getEnvironment() == 'profile') {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
        }

        return $bundles;
    }

    public function boot()
    {
        Service::setKernel($this);

        parent::boot();

        if (!$this->container->getParameter('bzion.miscellaneous.development')) {
            if ($this->getEnvironment() != 'prod' || $this->isDebug()) {
                throw new ForbiddenDeveloperAccessException(
                    'You are not allowed to access this page in a non-production ' .
                    'environment. Please change the "development" configuration ' .
                    'value and clear the cache before proceeding.'
                );
            }
        }

        if (in_array($this->getEnvironment(), array('profile', 'dev'), true)) {
            Debug::enable();
        }

        Service::setGenerator($this->container->get('router')->getGenerator());
        Service::setEnvironment($this->getEnvironment());
        Service::setModelCache(new ModelCache());

        // Ratchet doesn't support PHP's native session storage, so use our own
        // if we need it
        if (Service::getParameter('bzion.features.websocket.enabled') &&
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

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        if ($catch && !$this->isDebug()) {
            try {
                return $this->handleRaw($request, $type, $catch);
            } catch (Exception $e) {
                return $this->handleException($e, $request, $type);
            }
        } else {
            return $this->handleRaw($request, $type, $catch);
        }
    }

    private function handleRaw(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');
        $this->container->get('request_stack')->push($request);

        if ($type === self::MASTER_REQUEST) {
            $this->request = $request;
        }

        Service::setRequest($request);

        $event = new GetResponseEvent($this, $request, $type);
        $this->container->get('event_dispatcher')->dispatch(KernelEvents::REQUEST, $event);

        if ($request->attributes->get('_defaultHandler')) {
            return parent::handle($request, $type, $catch);
        }

        // An event may have given a response
        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $request, $type);
        }

        $session = $this->container->get('session');
        $session->start();
        Service::setFormFactory($this->container->get('form.factory'));

        $con = Controller::getController($request->attributes);
        $response = $con->callAction();

        return $this->filterResponse($response, $request, $type);
    }

    /**
     * Filters a response object.
     *
     * @param Response $response A Response instance
     * @param Request  $request  An error message in case the response is not a Response object
     * @param int      $type     The type of the request (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     *
     * @return Response The filtered Response instance
     */
    private function filterResponse(Response $response, Request $request, $type)
    {
        $event = new FilterResponseEvent($this, $request, $type, $response);
        $this->container->get('event_dispatcher')->dispatch(KernelEvents::RESPONSE, $event);

        $requestEvent = new FinishRequestEvent($this, $request, $type);
        $this->container->get('event_dispatcher')->dispatch(KernelEvents::FINISH_REQUEST, $requestEvent);

        return $event->getResponse();
    }

    public function terminate(Request $request, Response $response)
    {
        $this->container->get('event_dispatcher')->dispatch(
            KernelEvents::TERMINATE,
            new PostResponseEvent($this, $request, $response)
        );
    }

    public function terminateWithException(Exception $exception)
    {
        return false;
    }

    private function handleException(Exception $e, $request, $type)
    {
        $event = new GetResponseForExceptionEvent($this, $request, $type, $e);
        $this->container->get('event_dispatcher')->dispatch(KernelEvents::EXCEPTION, $event);

        // a listener might have replaced the exception
        $e = $event->getException();
        if (!$event->hasResponse()) {
            throw $e;
        }

        $response = $event->getResponse();

        if ($response->headers->has('X-Status-Code')) {
            // the developer asked for a specific status code
            $response->setStatusCode($response->headers->get('X-Status-Code'));
            $response->headers->remove('X-Status-Code');
        } elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
            // ensure that we actually have an error response
            if ($e instanceof HttpExceptionInterface) {
                // keep the HTTP status code and headers
                $response->setStatusCode($e->getStatusCode());
                $response->headers->add($e->getHeaders());
            } else {
                $response->setStatusCode(500);
            }
        }

        return $response;
    }
}
