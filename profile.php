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
	
	include ('api/player.php');
	
	$myPlayer = new player("guid", $_GET['id']);

	include ('includes/header.php');
	include ('includes/navigation.php');
?>

		<div class="playercontent">
			<div id="name"><?php echo $myPlayer->callsign; ?></div>
			<div id="team_name">Team:
<?php
			if ($myPlayer->teamID == 0)
				echo $myPlayer->teamName;
			else
				echo "<a href=\"viewteam.php?id=" . $myPlayer->teamID . "\">" . $myPlayer->teamName . "</a>";
?>
			</div>
			<div id="country">Country: <?php echo $myPlayer->country; ?></div>
			<a href="http://my.bzflag.org/bb/memberlist.php?mode=viewprofile&u=<?php echo $myPlayer->bzID; ?>"><div id="bzid"> BZID: <?php echo $myPlayer->bzID; ?> </div></a>
			<div id="joined">Joined: <?php echo $myPlayer->join_date; ?></div>
			<div id="button">&lt; Back </div>
			<div id="button">Message </div>
			<div id="button">Invite </div>
			<div id="button">Forums </div>
		</div>

		<script>
		  $(".menubar").hide(0).delay(100).fadeIn("slow")
		  $(".playercontent").hide(0).delay(300).fadeIn("slow")
		</script>
	</body> 
</html>