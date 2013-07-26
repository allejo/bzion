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
        $this->db = new Database();
    }

    /**
     * Draw the footer
     */
    function draw() {

    ?>

    </body>
</html>

    <?php

    }
}