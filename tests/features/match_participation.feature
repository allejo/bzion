Feature: Match Participation
  In order to keep track of match participation stats
  a player and team should have matches listed

  Scenario: Player alphonse should have win listed
    And there is a team called "Immutable Alchemy"
    And there is a player called "hughes"
    And there is a player called "mustang"
    And a new user called "alphonse" joins "Immutable Alchemy"
    And a new user called "edward" joins "Immutable Alchemy"
    And "Immutable Alchemy" plays a match against the red team with score 1 - 0 with players "alphonse,edward" on Team A and "hughes,mustang" on Team B
    When I go to "/players/alphonse/matches"
    Then I should see "Immutable Alchemy 1"
    And I should see "Red Team 0"
    When I go to "/players/alphonse/matches/wins"
    Then I should see "Immutable Alchemy 1"
    When I go to "/players/alphonse/matches/losses"
    Then I should not see "Immutable Alchemy 1"
    But I should see "There are no matches"

  Scenario: Teams should have a win and loss listed
    Given there is a team called "Octacious Freedom"
    And there is a team called "Bodacious Tables"
    And "Octacious Freedom" plays a match against "Bodacious Tables" with score 5 - 4
    When I go to "/teams/octacious-freedom"
    Then I should see "W 5 - 4 vs. Bodacious Tables"
    When I go to "/teams/octacious-freedom/matches"
    Then I should see "Octacious Freedom 5"
    And I should see "Bodacious Tables 4"
    When I go to "/teams/octacious-freedom/matches/wins"
    Then I should see "Octacious Freedom 5"
    When I go to "/teams/octacious-freedom/matches/losses"
    Then I should see "There are no matches"
    When I go to "/teams/octacious-freedom/matches/draws"
    Then I should see "There are no matches"
    When I go to "/teams/bodacious-tables"
    Then I should see "L 4 - 5 vs. Octacious Freedom"
    When I go to "/teams/bodacious-tables/matches"
    Then I should see "Bodacious Tables 4"
    When I go to "/teams/bodacious-tables/matches/wins"
    Then I should see "There are no matches"
    When I go to "/teams/bodacious-tables/matches/losses"
    Then I should see "Bodacious Tables 4"
    When I go to "/teams/bodacious-tables/matches/draws"
    Then I should see "There are no matches"
