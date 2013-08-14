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
        $baseUrl = "http://" . rtrim(HTTP_ROOT, '/');
    ?>
        </div> <!-- end .content -->

        <script src="<?php echo $baseUrl ?>/includes/strolljs/js/stroll.min.js"></script>
		<script>
			stroll.bind( '.group_list' );
		</script>

    </body>
</html>

    <?php

    }
}