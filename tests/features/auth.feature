Feature: Authentication
  In order to be identifiable
  As a guest
  I need to be able to log in and out

  Scenario: Bad Login
    Given I am logged out
    When I go to "/login"
    Then I should see "Bad Request"
    When I log in
    And I go to "/login?token=sample&username=samothy"
    Then I should see "You are already logged in"

  Scenario: Logging out
    Given I am logged in
    When I go to "/logout"
    Then I should be on the homepage
    Then I should see "You logged out successfully"
    When I go to "/profile"
    Then I should see "You need to be signed in"
