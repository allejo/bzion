Feature: Home Page

    Scenario: Visit home page
      Given I have entered a news article named "Significant News"
      And I have a custom page named "FAQ"
      When I go to the homepage
      Then I should see "Welcome"
      And I should see "Significant News"
      And I should see "FAQ"

    Scenario: Hide registration button
      Given I have a user
      When I go to the homepage
      Then I should see "Register"
      When I log in
      And I go to the homepage
      Then I should not see "Register"

