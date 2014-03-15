<?php

use Symfony\Component\HttpFoundation\Request;

if(session_id() == '') {
    // Session hasn't started
    session_start();
}


/**
 * The header used in HTML pages
 */
class Header
{

    /**
     * The title of the page
     * @var string
     */
    private $title;

    /**
     * The database variable used for queries
     * @var Database
     */
    private $db;

    /**
     * The browser's request
     * @var Request
     */
    private static $request;

    /**
     * Construct a new Header object
     * @param string $title The page's title
     */
    function __construct($title="") {
        $this->title = $title;
        $this->db = Database::getInstance();

        self::$request = Request::createFromGlobals();
    }

    /**
     * Draw the header
     * @param string $title The page's title
     */
    function draw($title="") {
        $title = (empty($title)) ? $this->title : $title;

        if (isset($_SESSION['username']))
        {
            $newMessage = Group::hasNewMessage($_SESSION['playerId']) ? "new_message" : "";
        }
        else
        {
            $newMessage = "";
        }

    ?>

<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <title><?= $title; ?></title>
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css">
        <link rel="stylesheet" href="<?= self::getBasePath(); ?>/includes/chosen/chosen.min.css">
        <link rel="stylesheet" href="<?= self::getBasePath(); ?>/includes/ladda/dist/ladda.min.css" />
        <link rel="stylesheet" href="<?= self::getBasePath(); ?>/assets/css/main.css">
    </head>

    <body>
        <header>
            <nav>
                <ul class="wrapper">
                    <li class="icon"><a href="<?= self::getBasePath(); ?>/"><i class="icon-home"></i></a></li>
                    <?php if (isset($_SESSION['username'])) { ?>
                    <li class="icon <?= $newMessage; ?>"><a href="<?= self::getBasePath(); ?>/messages"><i class="icon-comments"></i></a></li>
                    <?php } ?>
                    <li class="icon"><a href="<?= self::getBasePath(); ?>/news"><i class="icon-pushpin"></i></a></li>
                    <li><a href="<?= self::getBasePath(); ?>/teams">Teams</a></li>
                    <li><a href="<?= self::getBasePath(); ?>/players">Players</a></li>
                    <li><a href="<?= self::getBasePath(); ?>/matches">Matches</a></li>

                    <?php
                        $pages = Page::getPages();

                        foreach ($pages as $page)
                        {
                            if (!$page->isHomePage())
                            {
                                echo '<li><a href="' . $page->getURL() . '">' . $page->getName() . '</a></li>';
                            }
                        }
                    ?>

                    <li><a href="<?= self::getBasePath(); ?>/bans">Bans</a></li>
                    <li><a href="<?= self::getBasePath(); ?>/servers">Servers</a></li>

                    <?php
                        if (isset($_SESSION['username']))
                        {
                            echo '<li class="icon float right"><a href="' . self::getBasePath() . '/logout"><i class="icon-signout"></i></a></li>';
                            echo '<li class="icon float right"><a href="' . self::getBasePath() . '/profile"><i class="icon-user"></i></a></li>';
                            echo '<li class="icon float right"><a href="' . self::getBasePath() . '/notifications"><i class="icon-bell-alt"></i></a></li>';
                        }
                        else
                        {
                            $url = "http://my.bzflag.org/weblogin.php?action=weblogin&amp;url=";
                            $url .= urlencode(BASE_URL . "/login?token=%TOKEN%&username=%USERNAME%");

                            echo '<li class="icon float right"><a href="' . $url . '"><i class="icon-signin"></i></a></li>';
                        }
                    ?>

                    <div style="clear: both;"></div>
                </ul>
            </nav>

            <div class="notification">
                <i class="icon-ok"></i><span>Your message was sent successfully</span>
            </div>
        </header>

        <div class="page">
            <div class="content wrapper">

    <?php

    }

    /**
     * Redirect the page using PHP's header() function
     * @param string $location The page to redirect to
     * @param bool $override True if $location is an absolute path (e.g `http://google.com/`), false to prepend the base URL of the website to the path
     */
    public static function go($location = "/", $override = false) {
        if ($override) {
            header("Location: $location");
        } else if (strtolower($location) == "home" || $location == "/") {
            header("Location: " . self::getBasePath());
        } else {
            header("Location: " . self::getBasePath() . $location);
        }

        die();
    }

    /**
     * Returns the root path from which this request is executed.
     *
     * The base path never ends with a `/`.
     * @return string The raw path
     */
    public static function getBasePath() {
        if (!self::$request)
            self::$request = Request::createFromGlobals();

        return self::$request->getBasePath();
    }
}
