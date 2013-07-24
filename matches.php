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

		<table class="tableui">
			<tr id="match_tr">
				<th>Date</th>
				<th>Teams</th>
				<th>Result</th>
				<th>Referee</th>
			</tr>
<?php
			$matches = $myDatabase->query("SELECT timestamp, duration, teamOne_id, (SELECT name FROM bzion_teams WHERE tID = teamOne_id) as teamOne, teamOne_points, teamTwo_id, (SELECT name FROM bzion_teams WHERE tID = teamTwo_id) as teamTwo, teamTwo_points, uID, (SELECT callsign FROM bzion_players WHERE bzion_players.uID = bzion_matches.uID) as ref FROM bzion_matches ORDER BY timestamp DESC LIMIT 0, 30");
			
			foreach ($matches as $matchData)
			{
				echo "<tr id=\"match_tr\">";
				echo "	<td>" . $matchData['timestamp'] . " [" . $matchData['duration'] . "]</td>";
				echo "	<td><a href=\"viewteam.php?id=" . $matchData['teamOne_id'] . "\">" . $matchData['teamOne'] . "</a> vs <a href=\"viewteam.php?id=" . $matchData['teamTwo_id'] . "\">" . $matchData['teamTwo'] . "</a></td>";
				echo "	<td>" . $matchData['teamOne_points'] . "-" . $matchData['teamTwo_points'] . "</td>";
				echo "<td><a href=\"profile.php?id=" . $matchData['uID'] . "\">" . $matchData['ref'] . "</a></td>";
				echo "</tr>";
			}
?>
		</table>
	
		<script>
		  $(".menubar").hide(0).delay(100).fadeIn("slow")
		  $(".playercontent").hide(0).delay(300).fadeIn("slow")
		</script>
	</body> 
</html>
