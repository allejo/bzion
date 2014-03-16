<?php


if(session_id() == '') {
    // Session hasn't started
    session_start();
}


/**
 * The header used in HTML pages
 */
class Header extends Controller
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
     * Construct a new Header object
     * @param string $title The page's title
     */
    function __construct($title="") {
        $this->title = $title;
        $this->db = Database::getInstance();
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
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
        <link rel="stylesheet" href="<?= self::getBasePath(); ?>/includes/chosen/chosen.min.css">
        <link rel="stylesheet" href="<?= self::getBasePath(); ?>/includes/ladda/dist/ladda.min.css" />
        <link rel="stylesheet" href="<?= self::getBasePath(); ?>/assets/css/main.css">
    </head>

    <body>
        <header>
            <nav>
                <ul class="wrapper">
                    <li class="icon"><a href="<?= $this->generate("index") ?>"><i class="fa fa-home"></i></a></li>
                    <?php if (isset($_SESSION['username'])) { ?>
                    <li class="icon <?= $newMessage; ?>"><a href="<?= $this->generate("message_list") ?>"><i class="fa fa-comments"></i></a></li>
                    <?php } ?>
                    <li class="icon"><a href="<?= $this->generate("news_list") ?>"><i class="fa fa-thumb-tack"></i></a></li>
                    <li><a href="<?= $this->generate("team_list") ?>">Teams</a></li>
                    <li><a href="<?= $this->generate("player_list") ?>">Players</a></li>
                    <li><a href="<?= $this->generate("match_list") ?>">Matches</a></li>

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

                    <li><a href="<?= $this->generate("ban_list") ?>">Bans</a></li>
                    <li><a href="<?= $this->generate("server_list") ?>">Servers</a></li>

                    <?php
                        if (isset($_SESSION['username']))
                        {
                            echo '<li class="icon float right"><a href="' . $this->generate("logout") . '"><i class="fa fa-sign-out"></i></a></li>';
                            echo '<li class="icon float right"><a href="' . $this->generate("profile_show") . '"><i class="fa fa-user"></i></a></li>';
                            echo '<li class="icon float right"><a href="' . $this->generate("index") . '"><i class="fa fa-bell"></i></a></li>';
                        }
                        else
                        {
                            $url = "http://my.bzflag.org/weblogin.php?action=weblogin&amp;url=";
                            $url .= urlencode($this->generate("login", array(), true) . "?token=%TOKEN%&username=%USERNAME%");

                            echo '<li class="icon float right"><a href="' . $url . '"><i class="fa fa-sign-in"></i></a></li>';
                        }
                    ?>

                    <div style="clear: both;"></div>
                </ul>
            </nav>

            <div class="notification">
                <i class="fa fa-check"></i><span>Your message was sent successfully</span>
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
        return Service::getRequest()->getBasePath();
    }
}
