Feature: Teams
   In order to manage groups of players
   As a bzion user
   I need to be able to store and manage teams

   Scenario: List teams
      Given I have a team called "Fractional disguise"
      And I have a team called "Irresolute serpents"
      When I go to "/teams"
      Then I should see "Fractional disguise"
      And I should see "Irresolute serpents"
      And I should see "Teams" in the title

   Scenario: Show team
      Given I have a team called "Innkeeper Aquarium"
      And I have a user
      When I go to "/teams/innkeeper-aquarium"
      Then I should see "Innkeeper Aquarium"
      And I should see "Innkeeper Aquarium" in the title
      And I should see "Team" in the title
      And I should see "0 wins"
      But I should not see "Edit"
      And I should not see "Delete"
      And I should not see "Join"
      When I log in
      And I go to "/teams/innkeeper-aquarium"
      Then I should see "Join"

   Scenario: Play match
      Given I have a team called "Invalid Cisterna"
      And I have a team called "Ironclad Basin"
      And "Invalid Cisterna" plays a match against "Ironclad Basin" with score 5 - 2
      And "Ironclad Basin" plays a match against "Invalid Cisterna" with score 4 - 4
      When I go to "/teams"
      Then I should see "1 - 0 - 1"
      And I should see "0 - 1 - 1"
      When I go to "/teams/invalid-cisterna"
      Then I should see "1 wins"
      And I should see "1 draws"
      And I should see "0 losses"
      When I go to "/teams/ironclad-basin"
      Then I should see "1 losses"
      And I should see "1 draws"

    Scenario: Delete team
        Given I have a team called "Shatterproof Reservoir"
        And I have a team called "Parlous Provender"
        And I am an admin
        When I go to "/teams/shatterproof-reservoir"
        And I follow "Delete"
        And I press "Yes"
        Then I should be on "/teams"
        And I should see "The team Shatterproof Reservoir was deleted successfully"
        When I go to "/teams/parlous-provender"
        And I follow "Delete"
        And I press "No"
        Then I should be on "/teams/parlous-provender"
        When I log out
        And I go to "/teams"
        Then I should see "Parlous Provender"
        But I should not see "Shatterproof Reservoir"

    Scenario: Kick player
        Given I have a team called "Effervescent Duelists"
        And I have a team called "Geranium Cauldrons"
        And a new user called "Matchless" joins "Effervescent Duelists"
        And a new user called "Sans Pareil" joins "Geranium Cauldrons"
        And I am an admin
        When I go to "/teams/effervescent-duelists"
        Then I should see "Matchless"
        When I follow "Kick Matchless from team"
        And I press "Yes"
        Then I should be on "/teams/effervescent-duelists"
        When I reload the page
        Then I should not see "Matchless"
        When I go to "/teams/geranium-cauldrons"
        And I follow "Kick Sans Pareil from team"
        And I press "No"
        Then I should be on "/teams/geranium-cauldrons"
        When I reload the page
        Then I should see "Sans Pareil"

    Scenario: Abandon team
        Given I am logged in
        And I create a team called "Uninviting Bulwark"
        And I go to "/teams/uninviting-bulwark"
        And I follow "Abandon"
        Then I should see "You can't abandon the team"
        Given I am logged in as "irked"
        Given I am a member of a team called "Vexatious Vats"
        And I go to "/teams/vexatious-vats"
        Then I should see "irked"
        When I follow "Abandon"
        And I press "Yes"
        Then I should be on "/teams/vexatious-vats"
        And I should see "You have left"
        But I should not see "irked"
