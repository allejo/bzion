Feature: Home Page

    Scenario: Visit home page
      Given I have entered a news article named "Significant News"
      And I have a custom page named "FAQ"
      When I go to the home page
      Then I should see "Significant News"
      And I should see "FAQ"
