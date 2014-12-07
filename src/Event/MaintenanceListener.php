<?php
/**
 * This file contains a class that responds to an event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * An event that throws a maintenace page as a response to a request if we're
 * on maintenance mode
 */
class MaintenanceListener
{
    /**
     * Whether maintenance mode is enabled
     * @var boolean
     */
    private $maintenance;

    /**
     * Construct new MaintenanceListener
     * @param boolean $maintenance Whether maintenance mode is enabled
     */
    public function __construct($maintenance)
    {
        $this->maintenance = $maintenance;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $attributes = $event->getRequest()->attributes;

        if (!$this->maintenance || $attributes->get('_defaultHandler') || $attributes->get('_noMaint')) {
            return;
        }

        $controller = new \MaintenanceController($attributes);
        $event->setResponse($controller->callAction('show'));
    }
}
