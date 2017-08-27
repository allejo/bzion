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
    And I fill in "form_first_team_participants_player" with "uncherished, ..., , ,,, ,,,, Wildcat, uNmErCIFUl"
    And I press "Enter"
    Then I should see "You can't report a match where a team played against itself!"
    And I should see "This value should not be blank."
    And I should see "uncherished is not a member of Inimitable habitués"
    And I should see "There is no player called \"...\""
    And the "form_first_team_participants_player" field should contain "uncherished, ..., Wildcat, unmercifuL"
    But I should not see "The match was created successfully"

  Scenario: Enter Team vs Team match
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
    Then I should be on "/matches/1"
    And I should see "The match was created successfully"
    And I should see "Preeminent Cannoneers 1200 → 1225 16"
    And I should see "Subpar Fusillade 1200 → 1175 2"

  Scenario: Enter fun match
    Given I am an admin
    And there is a player called "convivial"
    And there is a player called "mirthful"
    And there is a player called "jocund"
    And there is a player called "sprightly"
    When I go to "/matches/enter"
    And I select "Red Team" from "form_first_team_team"
    And I fill in "5" for "form_first_team_score"
    And I select "Green Team" from "form_second_team_team"
    And I fill in "1" for "form_second_team_score"
    And I select "20" from "form_duration_0"
    And I fill in "form_first_team_participants_player" with "convivial,mirthful"
    And I fill in "form_second_team_participants_player" with "jocund,sprightly"
    And I select "Fun match" from "form_type"
    And I press "Enter"
    Then I should be on "/matches/2"
    And I should see "The match was created successfully"
    And I should see "Red Team 5"
    And I should see "Green Team 1"
    And I should see "20 minutes"
    And I should see "Fun Match"
    But I should not see "ELO Difference"

  Scenario: Enter Team vs Mixed match with the team winning
    Given I am an admin
    And there is a team called "Polymorphic wombats"
    And there is a player called "joyful bread"
    And there is a player called "angry hippos"
    And a new user called "gabaliumes" joins "Polymorphic wombats"
    And a new user called "tabloid" joins "Polymorphic wombats"
    When I go to "/matches/enter"
    And I select "Polymorphic wombats" from "form_first_team_team"
    And I fill in "4" for "form_first_team_score"
    And I select "Green Team" from "form_second_team_team"
    And I fill in "2" for "form_second_team_score"
    And I select "30" from "form_duration_0"
    And I fill in "form_first_team_participants_player" with "gabaliumes,tabloid"
    And I fill in "form_second_team_participants_player" with "joyful bread,angry hippos"
    And I press "Enter"
    Then I should be on "/matches/3"
    And I should see "The match was created successfully"
    And I should see "Polymorphic wombats 1200 → 1225 4"
    And I should see "Green Team 2"

  Scenario: Enter Team vs Mixed match without player roster for color team
    Given I am an admin
    And there is a team called "Numb3rs"
    When I go to "/matches/enter"
    And I select "Numb3rs" from "form_first_team_team"
    And I fill in "3" for "form_first_team_score"
    And I select "Green Team" from "form_second_team_team"
    And I fill in "0" for "form_second_team_score"
    And I select "30" from "form_duration_0"
    And I press "Enter"
    Then I should be on "/matches/enter"
    And I should see "A player roster is necessary for a color team for a mixed official match"

  Scenario: Enter fun match without player roster
    Given I am an admin
    When I go to "/matches/enter"
    And I select "Blue Team" from "form_first_team_team"
    And I fill in "3" for "form_first_team_score"
    And I select "Purple Team" from "form_second_team_team"
    And I fill in "2" for "form_second_team_score"
    And I select "15" from "form_duration_0"
    And I select "Fun match" from "form_type"
    And I press "Enter"
    Then I should be on "/matches/4"
    And I should see "Blue Team 3"
    And I should see "Purple Team 2"
    And I should see "15 minutes"
    And I should see "Fun Match"

  Scenario: Enter Mixed vs Mixed official match
    Given I am an admin
    And there is a player called "sands"
    And there is a player called "jonas"
    And there is a player called "cliff"
    And there is a player called "lard"
    When I go to "/matches/enter"
    And I select "Red Team" from "form_first_team_team"
    And I fill in "5" for "form_first_team_score"
    And I select "Green Team" from "form_second_team_team"
    And I fill in "3" for "form_second_team_score"
    And I fill in "form_first_team_participants_player" with "sands,jonas"
    And I fill in "form_second_team_participants_player" with "cliff,lard"
    And I select "30" from "form_duration_0"
    And I press "Enter"
    Then I should be on "/matches/5"
    And I should see "Red Team 5"
    And I should see "Green Team 3"
    And I should see "sands"
    And I should see "jonas"
    And I should see "cliff"
    And I should see "lard"
    But I should not see "→"
