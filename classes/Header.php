<?php

session_start();

class Header {

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
        $baseUrl = "http://" . rtrim(HTTP_ROOT, '/');

    ?>

<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/style.css">
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css">
    </head>
    <body>
    <div class="navbar"> 
    <div class="navmenu">
        <a href="<?php echo $baseUrl; ?>/" class="navbutton left"><i class="icon-home"></i></a> 
        <a href="<?php echo $baseUrl; ?>/news" class="navbutton left">News</a> 
        <a href="<?php echo $baseUrl; ?>/teams" class="navbutton left">Teams</a> 
        <a href="<?php echo $baseUrl; ?>/players" class="navbutton left">Players</a> 
        <a href="<?php echo $baseUrl; ?>/matches" class="navbutton left">Matches</a> 
        <?php

        $pages = Page::getPages();

        foreach ($pages as $key => $id) {
            $page = new Page($id);
            echo "<a href='" . $page->getURL() . "' class='navbutton left'>" . $page->getName() . "</a> ";
        }

        ?>
        <a href="<?php echo $baseUrl; ?>/bans" class="navbutton left">Bans</a> 
        <a href="<?php echo $baseUrl; ?>/servers" class="navbutton left">Servers</a> 
    <?php if (isset($_SESSION['username'])) { ?>
    <a href="<?php echo $baseUrl; ?>/logout.php" class="navbutton right">Logout</a>
        <a href="<?php echo $baseUrl; ?>/profile" class="navbutton right">Profile</a> 
        <?php
    } else {
        $url = "http://my.bzflag.org/weblogin.php?action=weblogin&url=";
        $url .= urlencode("http://" . rtrim(HTTP_ROOT, '/') . "/login.php?token=%TOKEN%&username=%USERNAME%");
        ?>
        <a href="<?php echo $url; ?>" class="navbutton right">Login</a>
    </div> <!-- end .navmenu -->
    <?php } ?>

    </div> <!-- end .navbar -->

    <div class="content">

    <?php

    }

    /**
     * Redirect the page using PHP's header() function
     * @param string $location The page to redirect to
     */
    public static function go($location = "/", $override = false) {
        $url = "http://" . rtrim(HTTP_ROOT, '/');
        if ($override) {
            header("Location: $location");
        } else if (strtolower($location) == "default" || strtolower($location) == "home" || strtolower($location) == "index.php" || strtolower($location) == "/") {
            header("Location: $url");
        } else {
            header("Location: $url" . $location);
        }
    }
}
