<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

require_once 'includes/checkToken.php';

class LoginController extends HTMLController
{
    public function loginAction(Request $request, Player $me)
    {
        if ($me->isValid())
            throw new ForbiddenException("You are already logged in!");

        $query = $request->query;
        $session = $request->getSession();

        $token = $query->get("token");
        $username = $query->get("username");

        if (!$token || !$username)
            throw new BadRequestException();

        // Don't check whether IPs match if we're on a development environment
        $checkIP = !DEVELOPMENT;
        $info = validate_token($token, $username, array(), $checkIP);

        if (!isset($info))
            throw new ForbiddenException("There was an error processing your login. Please go back and try again.");

        $session->set("username", $info['username']);
        $session->set("groups", $info['groups']);

        $redirectToProfile = false;

        if (!Player::playerBZIDExists($info['bzid'])) {
            // If they're new, redirect to their profile page so they can add some info
            $player = Player::newPlayer($info['bzid'], $info['username']);
            $redirectToProfile = true;
        } else {
            $player = Player::getFromBZID($info['bzid']);
        }

        $session->set("playerId", $player->getId());
        $player->updateLastLogin();

        Player::saveUsername($player->getId(), $info['username']);
        Visit::enterVisit($player->getId(),
                          $request->getClientIp(),
                          gethostbyaddr($request->getClientIp()),
                          $request->server->get('HTTP_USER_AGENT'),
                          $request->server->get('HTTP_REFERER'));

        if ($redirectToProfile) {
            $profile = Service::getGenerator()->generate('profile_show');

            return new RedirectResponse($profile);
        } else {
            return $this->goBack();
        }
    }

    public function logoutAction(Session $session)
    {
        $session->invalidate();
        $session->getFlashBag()->add('success', "You logged out successfully");

        // Don't redirect back but prefer going home, to prevent visiting
        // the login page (and logging in again, thus preventing the logout)
        // or other pages where authentication is required
        return $this->goHome();
    }

    public function loginAsTestUserAction(Session $session, Player $user)
    {
        if (!$user->isTestUser())
            throw new Exception("The player you specified is not a test user!");

        $session->set("playerId", $user->getId());
        $session->set("username", $user->getUsername());
    }
}
