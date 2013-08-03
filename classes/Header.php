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
        $title = ($title=="") ? $this->title : $title;

    ?>

<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
    <div class="navbar"> 
        <?php $baseUrl = "http://" . rtrim(HTTP_ROOT, '/'); ?>
        <a href="<?php echo $baseUrl; ?>/">Home</a> | 
        <a href="<?php echo $baseUrl; ?>/teams">Teams</a> | 
        <a href="<?php echo $baseUrl; ?>/players">Players</a> | 
        <a href="<?php echo $baseUrl; ?>/matches">Matches</a> | 

        <?php if (isset($_SESSION['username'])) { ?>
        <a href="<?php echo $baseUrl; ?>/profile">Profile</a> |
        <a href="logout.php">Logout [<?php echo $_SESSION['username']; ?>]</a>
        <?php } else {
            $url = "http://my.bzflag.org/weblogin.php?action=weblogin&url=";
            $url .= urlencode("http://" . rtrim(HTTP_ROOT, '/') . "/login.php?token=%TOKEN%&username=%USERNAME%");
        ?>
        <a href="<?php echo $url; ?>">Login</a>
	</div>
        <?php } ?>

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
        } else if (strtolower($location) == "default" || strtolower($location) == "index.php" || strtolower($location) == "/") {
            header("Location: $url");
        } else {
            header("Location: $url" . $location);
        }
    }
}
