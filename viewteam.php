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
	
	include ('api/team.php');
	
	$myTeam = new team($_GET['id']);

	include ('includes/header.php');
	include ('includes/navigation.php');
?>

		<div class="teampage_container">
			<div class="options_box">
				<div id="button_team_options"> Message Team </div>
				<div id="button_team_options"> Opponent Stats </div>
				<div id="button_team_options_disabled"> Edit Team </div>
			</div>
			
			<div id="teampage_team_name" style="font-family:HelveticaNeue-CondensedBold; font-size:55px;float:left;"><?php echo $myTeam->name; ?></div>
			<div style="clear:both;">
				<div id="rating" style="font-family:HelveticaNeue; font-size:20px;"> Rating: <?php echo $myTeam->score; ?> </div>
				<div id="team-leader" style="font-family:HelveticaNeue-Light; font-size:18px; margin-top: 10px;">Leader: <a href="profile.php?id=<?php echo $myTeam->leader_id; ?>"><?php echo $myTeam->leader_callsign; ?></a></div>

				<table class="match_stats">
					<tr id="stats_tr">
						<th>Played</th>
						<th>Won</th>
						<th>Tied</th>
						<th>Lost</th>
					</tr>
					<tr id="stats_tr">
						<td><?php echo $myTeam->matches_total; ?></td>
						<td><?php echo $myTeam->matches_won; ?></td>
						<td><?php echo $myTeam->matches_tied; ?></td>
						<td><?php echo $myTeam->matches_lost; ?></td>
					</tr>
				</table>
				
				<div id="description" style="font-family:HelveticaNeue-CondensedBold; font-size:30px; margin-top:20px;">Team Description </div>
				<div id="team_desc"><?php echo $myTeam->description; ?></div>
				
				<div id="teamers" style="font-family:HelveticaNeue-CondensedBold; font-size:30px; margin-top:20px;">Members</div>
				<table class ="member_table">
					<tr id="title_tr">
						<th> Name </th>
						<th> Country </th>
					</tr>
<?php
					foreach ($myTeam->getMembers() as $teamMember)
					{
						echo "					<tr id=\"member_tr\">\n";
						echo "    					<td><a href=\"profile.php?id=" . $teamMember['uID'] . "\">" . $teamMember['callsign'] . "</a></td>\n";
						echo "    					<td>" . $teamMember['country'] . "</td>\n";
						echo "					</tr>\n";
					}
?>
				</table>
				
<div id="teamers" style="font-family:HelveticaNeue-CondensedBold; font-size:30px; margin-top:20px;">Recent matches</div>
<table class="previous_matches">
<tr id="match2_tr">
<th> Match </th>
<th> Result </th>
</tr>
<tr id="match2_tr">
<td> Loki vs BattleZoneBrothers(BZB) </td>
<td> 8-4 </td>
</tr>
<tr id="match2_tr">
<td> Loki vs BattleZoneBrothers(BZB) </td>
<td> 8-1 </td>
</tr>
<tr id="match2_tr">
<td> Firebirds vs Loki </td>
<td> 2-2 </td>
</tr>
</table>
</div>
</div>

		</table>
		<script src="js/jquery.jeditable.js"></script>
		<script>
			$(".menubar").hide(0).delay(100).fadeIn("slow");
			$(".playercontent").hide(0).delay(300).fadeIn("slow");
		</script>
	</body>
</html>
