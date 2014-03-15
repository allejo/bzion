<?php

/**
 * The footer used for HTML pages
 */
class Footer {

    /**
     * The database variable used for queries
     * @var Database
     */
    private $db;

    /**
     * A list of javascript URLs to include in the page
     * @var Array
     */
    private $scripts;

    /**
     * Construct a new Footer object
     */
    function __construct() {
        $this->db = Database::getInstance();
        $this->scripts = array();
    }

    /**
     * Add a new JS script to the list
     * @param string $url The URL of the script, relative to the base URL (e.g: js/messages.js)
     */
    public function addScript($url) {
        $url = ltrim($url, "/");
        $this->scripts[] = $url;
    }

    /**
     * Draw the footer
     */
    function draw() {
    ?>
            </div>
        </div> <!-- end .content -->

                <script>
            var baseURL = "<?php echo Header::getBasePath(); ?>";
                </script>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
        <script src="<?php echo Header::getBasePath(); ?>/assets/js/main.js"></script>

        <?php
            foreach($this->scripts as $url) {
                echo "<script src=\"" .Header::getBasePath() . "/$url\"></script>";
            }
        ?>
    </body>
</html>

    <?php

    }
}
