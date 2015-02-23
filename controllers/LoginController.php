<?php

use BZIon\Composer\ConfigHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../includes/checkToken.php';

class LoginController extends HTMLController
{
    public function loginAction(Request $request, Player $me)
    {
        if ($me->isValid()) {
            throw new ForbiddenException("You are already logged in!");
        }

        $query = $request->query;
        $session = $request->getSession();

        $token = $query->get("token");
        $username = $query->get("username");

        if (!$token || !$username) {
            throw new BadRequestException();
        }

        // Don't check whether IPs match if we're on a development environment
        $checkIP = !$this->isDebug()&&0;
        $info = validate_token($token, $username, array(), $checkIP);

        if (!isset($info)) {
            throw new ForbiddenException("There was an error processing your login. Please go back and try again.");
        }

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

        $player->setUsername($info['username']);
        Visit::enterVisit($player->getId(),
                          $request->getClientIp(),
                          gethostbyaddr($request->getClientIp()),
                          $request->server->get('HTTP_USER_AGENT'),
                          $request->server->get('HTTP_REFERER'));
        $this->configPromoteAdmin($player);

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
        if (!$user->isTestUser()) {
            throw new Exception("The player you specified is not a test user!");
        }

        $session->set("playerId", $user->getId());
        $session->set("username", $user->getUsername());

        return $this->goHome();
    }

    /**
     * Promote a player to an admin if the configuration file specifies so
     *
     * @param Player $player The player in question
     */
    private function configPromoteAdmin(Player $player)
    {
        $adminUsername = $this->container->getParameter('bzion.miscellaneous.admin');

        if (!$adminUsername) {
            return;
        }

        if (strtolower($player->getUsername()) === strtolower($adminUsername)) {
            $player->addRole(Player::DEVELOPER);

            // Remove the username from the configuration file so that we don't
            // give admin permissions to the wrong person in case callsign
            // changes take place. This is supposed to happen only once, so we
            // don't need to worry about the performance overhead due to the
            // parsing and dumping of the YML file
            $path = ConfigHandler::getConfigurationPath();
            $config = Yaml::parse($path);
            $config['bzion']['miscellaneous']['admin'] = null;
            file_put_contents($path, Yaml::dump($config, 4));

            $this->getLogger()->notice(sprintf(
                "User %s with BZID %s is now an administrator, as instructed by the configuration file",
                $adminUsername,
                $player->getBZID()
            ));
        }
    }
}
