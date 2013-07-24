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
	
	$myConnection = new database();

	include ('includes/header.php');
	include ('includes/navigation.php');
?>

		<div class="main_body_text">
		<div id="field_placeholder" style="text-align:center;">
			<input id="field" type="text" placeholder="Team name">  
			<br>
			<br>
			<textarea rows="14", cols="60" id="text_content">Team Description</textarea> 
			<br>
			<br>
			<label>Want people to join without an invite?<input id="team_join_status" type="checkbox" value="yes"></label>
			<br>
			<br>
			<a href="#" class="button_save">Save</a>
			<a href="#" class="button_save">Discard</a>
		</div>
		</div>
		
		<script src="js/jquery.jeditable.js"></script>
		<script>
			$(".menubar").hide(0).delay(100).fadeIn("slow")
			$(".main_body_text").hide(0).delay(300).fadeIn("slow")
		</script>
	</body> 
</html>
