<?php

include("bzion-load.php");

$header = new Header("Themes");
$header->draw();
$baseUrl = "http://" . rtrim(HTTP_ROOT, '/');

?>
<html>
<head>
<script>


  button_click = function(){
      cookie_save($( ".themes option:selected" ).text());
  }
  cookie_save = function(val){
      console.log(val);
      if ($.cookie('theme') == null){
        console.log("keine cookie");
        $.cookie('theme', val, {expires: 10, path:'/'});
      }else{
        $.removeCookie('theme')
        $.cookie('theme', val, {expires: 10, path: '/'});
        console.log("cookie ist");
      }
  }
  </script>
    </head>
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
</body>

</html>
