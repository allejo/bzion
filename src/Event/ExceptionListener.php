<?php
/**
 * This file contains a class that responds to an event
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as BaseListener;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Handles exceptions
 */
class ExceptionListener extends BaseListener
{
    /**
     * {@inheritdoc}
     */
    protected function duplicateRequest(\Exception $exception, Request $request)
    {
        $attributes = array(
            '_controller' => 'Error',
            '_action'     => 'error',
            'logger'      => $this->logger instanceof DebugLoggerInterface ? $this->logger : null,
            'exception'   => $exception
        );

        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');
        return $request;
    }

     /**
      * {@inheritdoc}
      */
     protected function logException(\Exception $exception, $message, $original = true)
     {
         $isCritical = !$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500;
         $context = array('exception' => $exception);
         if (null !== $this->logger) {
             if ($isCritical) {
                 $this->logger->critical($message, $context);
             } else {
                 $this->logger->warning($message, $context);
             }
         } elseif (!$original || $isCritical) {
             error_log($message);
         }
     }
}
