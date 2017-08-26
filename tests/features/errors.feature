Feature: Error messages
  As a user who performed an erroneous request
  I need to see a description of the error
  In order to readjust my request

  Scenario: 404 error page
    When I go to "sampel/errorrrrr/pgae/apre/parege/refresh/page"
    Then the response status code should be 404
    And I should see "Sorry, the page you are looking for could not be found."
    And I should see "Error" in the title

  Scenario: 404 model not found
    Given there is a player called "illusory"
    Given there is no player called "illusory"
    When I go to "players/illusory"
    Then the response status code should be 404
    And I should see "The specified player could not be found"
    And I should see "Players" in the title

  Scenario: Access forbidden
    Given I am logged out
    When I go to "bans/new"
    Then I should see "You are not allowed to create a new ban"
    And I should see "Error" in the title
    But the response status code should be 200
