<?php

include("bzion-load.php");

$header = new Header("Themes");
$header->draw();

?>

<form class="theme_selector">
    <select class="themes">
        <option>Industrial</option>
        <option>Colorful</option>
    </select>
    <button class="theme_submit" onClick="button_click()">Select!</button>
</form>

<?php

$footer = new Footer();
$footer->draw();

?>
