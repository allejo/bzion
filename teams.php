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
	
	$myTeams = $myConnection->query("SELECT tID, leaderID, (SELECT callsign FROM bzion_players WHERE uID = leaderID) AS callsign, name, score, member_count, activity, total FROM bzion_teams WHERE active = 1 ORDER BY score DESC");
?>

		<div class="team_content">
			<table class="teams_table">
				<tr id="teams_tr">
					<th style="width:30%"> Name </th>
					<th> Rating </th>
					<th> Leader </th> 
					<th> Members </th>
					<th> Activity </th>
					<th> Matches </th>
				</tr>
<?php
				foreach ($myTeams as $team)
				{
					if ($team['activity'] > 0)
					{
						echo "				<tr id=\"teams_tr\">\n";
						echo "				    <td><a href=\"viewteam.php?id=" . $team['tID'] . "\">" . $team['name'] . "</a></td>\n";
						echo "				    <td>" . $team['score'] . " <img src=\"imgs/rankicons/" . floor($team['score']/100)*100 . ".png\" style=\"float:right;\"></td>\n";
						echo "				    <td><a href=\"profile.php?id=" . $team['leaderID'] . "\">" . $team['callsign'] . "</a></td>\n";
						echo "				    <td>" . $team['member_count'] . "</td>\n";
						echo "				    <td>" . $team['activity'] . "</td>\n";
						echo "				    <td>" . $team['total'] . "</td>\n";
						echo "				</tr>\n";
					}
				}
?>

			</table>
			<table class="teams_table">
				<tr id="teams_tr">
					<th style="width:30%"> Name </th>
					<th> Rating </th>
					<th> Leader </th> 
					<th> Members </th>
					<th> Activity </th>
					<th> Matches </th>
				</tr>
<?php
				foreach ($myTeams as $team)
				{
					if ($team['activity'] == 0)
					{
						echo "				<tr id=\"teams_tr\">\n";
						echo "				    <td><a href=\"viewteam.php?id=" . $team['tID'] . "\">" . $team['name'] . "</a></td>\n";
						echo "				    <td>" . $team['score'] . " <img src=\"imgs/rankicons/" . floor($team['score']/100)*100 . ".png\" style=\"float:right;\"></td>\n";
						echo "				    <td><a href=\"profile.php?id=" . $team['leaderID'] . "\">" . $team['callsign'] . "</a></td>\n";
						echo "				    <td>" . $team['member_count'] . "</td>\n";
						echo "				    <td>" . $team['activity'] . "</td>\n";
						echo "				    <td>" . $team['total'] . "</td>\n";
						echo "				</tr>\n";
					}
				}
?>

			</table>
		</div>
		<script>
		  $(".menubar").hide(0).delay(100).fadeIn("slow")
		  $("td").hide(0).delay(300).fadeIn("slow")
		</script>
	</body> 
</html>
