Feature: Matches
   In order to promote competition
   As a league team
   we need to be able to record matches against other teams

   Scenario: Enter match - Denial of Access
      Given I am logged out
      When I go to "/matches/enter"
      Then I should see "You are not allowed to create a new match"

   Scenario: Enter match - Invalid data
       Given I am an admin
       And there is a team called "Inimitable habitués"
       And there is a player called "uncherished"
       And a new user called "Wildcat" joins "Inimitable habitués"
       And a new user called "unmercifuL" joins "Inimitable habitués"
       When I go to "/matches/enter"
       And I select "Inimitable habitués" from "form_first_team_team"
       And I select "Inimitable habitués" from "form_second_team_team"
       And I fill in "form_first_team_participants_players" with "uncherished, ..., , ,,, ,,,, Wildcat, uNmErCIFUl"
       And I press "Enter"
       Then I should see "You can't report a match where a team played against itself!"
       And I should see "This value should not be blank."
       And I should see "uncherished is not a member of Inimitable habitués"
       And I should see "There is no player called ..."
       And the "form_first_team_participants_players" field should contain "uncherished, unmercifuL, Wildcat"
       But I should not see "The match was created successfully"

   Scenario: Enter match
       Given I am an admin
       And there is a team called "Preeminent Cannoneers"
       And there is a team called "Subpar Fusillade"
       When I go to "/matches/enter"
       And I select "Preeminent Cannoneers" from "form_first_team_team"
       And I fill in "16" for "form_first_team_score"
       And I select "Subpar Fusillade" from "form_second_team_team"
       And I fill in "2" for "form_second_team_score"
       And I select "30" from "form_duration_0"
       And I press "Enter"
       Then I should be on "/matches"
       And I should see "The match was created successfully"
       And I should see "Preeminent Cannoneers 16"
       And I should see "2 Subpar Fusillade"
