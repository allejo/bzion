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
      Given I have a team called "Inkeeper Aquarium"
      And I have a user
      When I go to "/teams/inkeeper-aquarium"
      Then I should see "Inkeeper Aquarium"
      And I should see "Inkeeper Aquarium" in the title
      And I should see "Team" in the title
      And I should see "0 wins"
      But I should not see "Edit"
      And I should not see "Delete"
      And I should not see "Join"
      When I log in
      And I go to "/teams/inkeeper-aquarium"
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
