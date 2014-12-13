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
        if (!$this->maintenance) {
            return;
        }

        $attributes = $event->getRequest()->attributes;

        // Some paths (like /login or the profiler) might not want to be blocked
        // on maintenance
        if ($attributes->get('_defaultHandler') || $attributes->get('_noMaint')) {
            return;
        }

        $controller = new \MaintenanceController($attributes);

        // Admins should be able to see the website even on maintenance
        if ($controller->getMe()->hasPermission(\Permission::BYPASS_MAINTENANCE)) {
            return;
        }

        $event->setResponse($controller->callAction('show'));
    }
}
