<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

require_once 'includes/checkToken.php';

class LoginController extends HTMLController
{
    public function loginAction(Request $request)
    {
        $query = $request->query;
        $session = $request->getSession();

        if (!$query->has("token") || !$query->has("username")) {
            throw new BadRequestException();
        }

        $token = $query->get("token");
        $username = $query->get("username");

        // Don't check whether IPs match if we're on a development environment
        $checkIP = !DEVELOPMENT;
        $info = validate_token($token, $username, array(), $checkIP);

        if (isset($info)) {
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
        } else {
            throw new BadRequestException("Idiot, type it right");
        }
    }

    public function logoutAction(Request $request)
    {
        $request->getSession()->invalidate();

        return $this->goBack();
    }

    public function loginAsTestUserAction(Session $session, Player $user)
    {
        if (!$user->isTestUser())
            throw new Exception("The player you specified is not a test user!");

        $session->set("playerId", $user->getId());
    }

}
