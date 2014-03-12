<?php

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
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>/includes/chosen/chosen.min.css">
        <link rel="stylesheet" href="<?= BASE_URL; ?>/includes/ladda/dist/ladda.min.css" />
        <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/main.css">
    </head>

    <body>
        <header>
            <nav>
                <ul class="wrapper">
                    <li class="icon"><a href="<?= BASE_URL; ?>/"><i class="icon-home"></i></a></li>
                    <?php if (isset($_SESSION['username'])) { ?>
                    <li class="icon <?= $newMessage; ?>"><a href="<?= BASE_URL; ?>/messages"><i class="icon-comments"></i></a></li>
                    <?php } ?>
                    <li class="icon"><a href="<?= BASE_URL; ?>/news"><i class="icon-pushpin"></i></a></li>
                    <li><a href="<?= BASE_URL; ?>/teams">Teams</a></li>
                    <li><a href="<?= BASE_URL; ?>/players">Players</a></li>
                    <li><a href="<?= BASE_URL; ?>/matches">Matches</a></li>

                    <?php
                        $pages = Page::getPages();

                        foreach ($pages as $key => $id)
                        {
                            $page = new Page($id);

                            if (!$page->isHomePage())
                            {
                                echo '<li><a href="' . $page->getURL() . '">' . $page->getName() . '</a></li>';
                            }
                        }
                    ?>

                    <li><a href="<?= BASE_URL; ?>/bans">Bans</a></li>
                    <li><a href="<?= BASE_URL; ?>/servers">Servers</a></li>

                    <?php
                        if (isset($_SESSION['username']))
                        {
                            echo '<li class="icon float right login_logout"><a href="' . BASE_URL . '/logout.php"><i class="icon-signout"></i></a></li>';
                            echo '<li class="icon float right"><a href="' . BASE_URL . '/profile"><i class="icon-user"></i></a></li>';
                            echo '<li class="icon float right"><a href="' . BASE_URL . '/notifications"><i class="icon-bell-alt"></i></a></li>';
                        }
                        else
                        {
                            $url = "http://my.bzflag.org/weblogin.php?action=weblogin&amp;url=";
                            $url .= urlencode(BASE_URL . "/login.php?token=%TOKEN%&username=%USERNAME%");

                            echo '<li class="icon float right login_logout"><a href="' . $url . '"><i class="icon-signin"></i></a></li>';
                        }
                    ?>

                    <div style="clear: both;"></div>
                </ul>
            </nav>

            <div class="notification">
                <i class="icon-ok"></i><span>Your message was sent successfully</span>
            </div>
        </header>

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
            header("Location: " . BASE_URL);
        } else {
            header("Location: " . BASE_URL . $location);
        }

        die();
    }
}
