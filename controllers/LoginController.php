<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

require_once 'includes/checkToken.php';

class LoginController extends HTMLController
{
    public function loginAction(Request $request)
    {
        $query = $request->query;
        $session = $request->getSession();

        if (!$query->has("token") || !$query->has("username")) {
            Header::go("home");
        }

        $token = $query->get("token");
        $username = $query->get("username");

        // Don't check whether IPs match if we're on a development environment
        $checkIP = !DEVELOPMENT;
        $info = validate_token($token, $username, array(), $checkIP);

        if (isset($info)) {
            $session->set("username", $info['username']);
            $session->set("groups", $info['groups']);

            $go = "home";

            if (!Player::playerBZIDExists($info['bzid'])) {
                $player = Player::newPlayer($info['bzid'], $info['username']);
                $go = "/profile"; // If they're new, redirect to their profile page so they can add some info
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

            Header::go($go);

        } else {
            return "There was an error processing your login. Please go back and try again.";
        }
    }

    public function logoutAction(Request $request)
    {
        $request->getSession()->invalidate();

        $loc = "/";
        $override = false;

        if ($request->server->has('HTTP_REFERER')) {
            $loc = $request->server->get('HTTP_REFERER');
            $override = true;
        }

        Header::go($loc, $override);
    }

    public function loginAsTestUserAction(Session $session, Player $user)
    {
        if (!$user->isTestUser())
            throw new Exception("The player you specified is not a test user!");

        $session->set("playerId", $user->getId());
    }

}
