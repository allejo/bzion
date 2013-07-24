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

		<div class="news_content">
			<div id="add_button"></div>
			<div id="compose_page_content">
				<input id="field" type="text" placeholder="Title">
				<br>
				<br>
				<textarea rows="14", cols="60" id="text_content">Enter content here</textarea> 
			</div>

<?php
			$newsResults = $myDatabase->query("SELECT title, authorID, callsign, content, timestamp FROM bzion_news JOIN bzion_players ON bzion_news.authorID = bzion_players.uID ORDER BY bzion_news.timestamp DESC LIMIT 0, 10");

			foreach ($newsResults as $article)
			{
				echo "			<div class=\"news_box\">
					<div class=\"title_and_author\">
					<div id=\"news_title\">" . $article['title'] . "</div> <br>
					<div id=\"author\"><a href=\"profile.php?id=" . $article['authorID'] . "\">" . $article['callsign'] . "</a></div>
					<div id=\"date_of_add\">" . $article['timestamp'] . "</div> 
				</div>
				<br>
				<div class=\"content\">" . $article['content'] . "</div>
			</div>\n";
			}
?>
		</div>
		
		<script src="js/jquery.jeditable.js"></script>
		<script>
			$(document).ready(function()
			{
				var clickcount=0; 
				
				$("#add_button").click(function()
				{
					$("#compose_page_content").fadeToggle("fast");
				});
				
				$(".content").editable("save.php",{ type: "textarea", cancel: "cancel", submit:"OK", tooltip:"click to edit!" });
			});
		
		    $(".menubar").hide(0).delay(100).fadeIn("slow");
		    $("td").hide(0).delay(300).fadeIn("slow");
		</script>
	</body> 
</html>
