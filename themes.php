<?php

include("bzion-load.php");

$header = new Header("Themes");
$header->draw();
$baseUrl = "http://" . rtrim(HTTP_ROOT, '/');

?>
<form class="theme_selector">
<select class="themes">
<option>Industrial</option>
<option>Colorful</option>
</select>
<div class="theme_submit" onClick="button_click()"> Select! </div>
</form>
    <?php

$footer = new Footer();
$footer->draw();

?>
