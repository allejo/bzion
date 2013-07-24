<?php
	/*
		Copyright 2013 Ashvala Vinay and Vladimir Jimenez
		
		Permission is hereby granted, free of charge, to any person obtaining
		a copy of this software and associated documentation files (the
		"Software"), to deal in the Software without restriction, including
		without limitation the rights to use, copy, modify, merge, publish,
		distribute, sublicense, and/or sell copies of the Software, and to
		permit persons to whom the Software is furnished to do so, subject to
		the following conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
		MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
		LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
		OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
		WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	*/
	
	include ('api/database.php');
	
	$myDatabase = new database();

	include ('includes/header.php');
	include ('includes/navigation.php');
?>

		<div class="faq_content">
<?php
			$currentFile = $_SERVER["PHP_SELF"];
			$parts = explode('/', $currentFile);
			$thisPage = $parts[count($parts) - 1];
			
			$pageData = $myDatabase->query("SELECT content FROM bzion_pages WHERE page_name = '" . $thisPage . "'");
			echo $pageData['content'];
?>
		</div>

		<script src="js/jquery.jeditable.js"></script>
		<script>
			$(document).ready(function(){
			 $(".faq_content").editable("save.php",{ type: "textarea", cancel: "cancel", submit:"OK", tooltip:"click to edit!" });
			});
		
			$(".menubar").hide(0).delay(100).fadeIn("slow");
			$(".faq_content").hide(0).delay(300).fadeIn("slow");
		</script>
	</body>
</html>
