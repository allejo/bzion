<?php
/**
 * This file contains a class that responds to events
 *
 * @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
 */

namespace BZIon\Event;

use BZIon\NotificationAdapter\WebSocketAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * An event subscriber for bzion events
 */
class EventSubscriber implements EventSubscriberInterface {
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * Constructor
     *
     * You will probably not need to instantiate an object of this class,
     * Symfony already does the hard work for us
     *
     * @param \Swift_Mailer $mailer The mailer
     */
    public function __construct(\Swift_Mailer $mailer) {
        $this->mailer = $mailer;
        $this->twig   = \Service::getTemplateEngine();
    }

    /**
     * Returns all the events that this subscriber handles, and which method
     * handles each one
     *
     * @return string
     */
    public static function getSubscribedEvents()
    {
        return array(
            'message.new'  => 'onNewMessage',
            'team.delete'  => 'notify',
            'team.abandon' => 'notify',
            'team.kick'    => 'notify',
            'team.join'    => 'notify',
            'team.invite'  => 'notify',
            'team.leader_change' => 'notify',
            'welcome'      => 'notify',
        );
    }

    /**
     * Called every time a new message is sent
     * @param NewMessageEvent $event The event
     */
    public function onNewMessage(NewMessageEvent $event)
    {
        // Get a list of everyone who can see the message so we can notify them -
        // the sender of the message is excluded
        $group = $event->getMessage()->getGroup();
        $author = $event->getMessage()->getAuthor()->getId();
        $recipients = $group->getReadMemberIDs($author);

        // The websocket will handle emails if it is enabled
        if (!WebSocketAdapter::isEnabled()) {
            $this->sendEmails(
                'New message received',
                $recipients,
                'message',
                array('message' => $event->getMessage())
            );
        }

        $event->getMessage()->getGroup()->markUnread($author);
        \Notification::pushEvent('message', array(
            'message' => $event->getMessage(),
            'recipients' => $recipients
        ));
    }

    /**
     * Called when an event needs to notify a user
     * @param Event $event The event
     * @param string $type The event's type
     */
    public function notify(Event $event, $name)
    {
        $event->notify($name);
    }

    /**
     * Send emails to a list of recipients
     *
     * @param string $subject The subject of the messages
     * @param \int[] $recipients The IDs of the players to which the messages will be sent
     * @param string $template The twig template name for the e-mail body
     * @param array $params Any extra parameters to pass to twig
     * @return void
     */
    public function sendEmails($subject, $recipients, $template, $params = array())
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array(EMAIL_FROM => SITE_TITLE))
            ->setBody($this->twig->render("Email/$template.txt.twig",  $params))
            ->addPart($this->twig->render("Email/$template.html.twig", $params), 'text/html')
        ;

        foreach ($recipients as $recipient) {
            $recipient = new \Player($recipient);

            if (!$recipient->isVerified()) {
                continue;
            }

            $message->setTo($recipient->getEmailAddress());
            $this->mailer->send($message);
        }
    }
}
