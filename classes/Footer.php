<?php

class Footer {

    /**
     * The database variable used for queries
     * @var Database
     */
    private $db;

    /**
     * Construct a new Footer object
     */
    function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Draw the footer
     */
    function draw() {

    ?>
        </div> <!-- end .content -->
    </body>
</html>

    <?php

    }
}