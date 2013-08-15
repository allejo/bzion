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

		<script>
            var baseURL = "<?php echo $baseUrl; ?>";
		</script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js"></script>
        <!--<script src="<?php echo $baseUrl ?>/includes/strolljs/js/stroll.min.js"></script>-->
        <script src="<?php echo $baseUrl ?>/includes/niftyjs/js/templates.js"></script>
        <script src="<?php echo $baseUrl ?>/includes/niftyjs/js/Nifty.js"></script>
        <script src="<?php echo $baseUrl ?>/includes/ladda/js/spin.js"></script>
        <script src="<?php echo $baseUrl ?>/includes/ladda/js/ladda.js"></script>
        <script src="<?php echo $baseUrl ?>/js/javascript.js"></script>

    </body>
</html>

    <?php

    }
}
