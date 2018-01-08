Feature: Bans
  In order to keep a peaceful environment
  bans are occasionally need to enacted

  Scenario: Enter ban without having permission
    Given I am logged out
    When I go to "/bans/new"
    Then I should see "You are not allowed to create a new ban"

  Scenario: Enter ban with invalid data
    Given I am an admin
    And there is a player called "alric"
    When I go to "/bans/new"
    And I fill in "form_player_player" with "alric"
    And I press "Enter Ban"
    Then I should see "This value should not be blank"

  Scenario: Enter ban
    Given I am an admin
    And there is a player called "alrican"
    When I go to "/bans/new"
    And I fill in "form_player_player" with "alrican"
    And I fill in "form_reason" with "Just another ordinary ban"
    And I check "form_is_permanent"
    And I press "Enter Ban"
    Then I should be on "/bans/1"
    And I should see "alrican"
    And I should see "Ban Reason Just another ordinary ban"
    And I should see "Expiration Until further notice"
    And I should see "Banned IP Addresses No IPs affected by this ban"
