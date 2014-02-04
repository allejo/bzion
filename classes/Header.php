<?php

session_start();

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

    ?>

<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/includes/chosen/chosen.min.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/includes/ladda/dist/ladda.min.css" />
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    </head>

    <body>
    <div class="navbar">
    <div class="navmenu">
        <a href="<?php echo BASE_URL; ?>/" class="navbuttonicon left"><i class="icon-home"></i></a>
        <?php if (isset($_SESSION['username'])) {

        if (Group::hasNewMessage($_SESSION['bzid'])) {
            $new = "new_message";
        }
        ?>
        <a href="<?php echo BASE_URL; ?>/messages" class="navbuttonicon left <?php echo $new; ?>"><i class="icon-comments"></i></a>
        <?php } ?>
        <a href="<?php echo BASE_URL; ?>/news" class="navbuttonicon left"><i class="icon-pushpin"></i></a>
        <a href="<?php echo BASE_URL; ?>/teams" class="navbutton left">Teams</a>
        <a href="<?php echo BASE_URL; ?>/players" class="navbutton left">Players</a>
        <a href="<?php echo BASE_URL; ?>/matches" class="navbutton left">Matches</a>
        <?php

        $pages = Page::getPages();

        foreach ($pages as $key => $id) {
            $page = new Page($id);
            if (!$page->isHomePage())
                echo "<a href='" . $page->getURL() . "' class='navbutton left'>" . $page->getName() . "</a> ";
        }

        ?>
        <a href="<?php echo BASE_URL; ?>/bans" class="navbutton left">Bans</a>
        <a href="<?php echo BASE_URL; ?>/servers" class="navbutton left">Servers</a>
    <?php if (isset($_SESSION['username'])) { ?>
        <a href="<?php echo BASE_URL; ?>/logout.php" class="navbuttonicon right"><i class="icon-signout"></i></a>
        <a href="<?php echo BASE_URL; ?>/profile" class="navbuttonicon right"><i class="icon-user"></i></a>
        <a href="<?php echo BASE_URL; ?>/notifications" class="navbuttonicon right"><i class="icon-bell-alt"></i></a>
        <?php
    } else {
        $url = "http://my.bzflag.org/weblogin.php?action=weblogin&amp;url=";
        $url .= urlencode(BASE_URL . "/login.php?token=%TOKEN%&username=%USERNAME%");
        ?>
        <a href="<?php echo $url; ?>" class="navbuttonicon right"><i class="icon-signin"></i></a>
    <?php } ?>
    </div> <!-- end .navmenu -->

    </div> <!-- end .navbar -->

    <div class="notification">
        <i class="icon-ok"></i><span>Your message was sent successfully</span>
    </div>

    <div class="content">

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
        } else if (strtolower($location) == "default" || strtolower($location) == "home" || strtolower($location) == "index.php" || strtolower($location) == "/") {
            header("Location: " . BASE_URL);
        } else {
            header("Location: " . BASE_URL . $location);
        }

        die();
    }
}
