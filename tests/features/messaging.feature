Feature: Messaging
   In order to make secret plans
   As a GU league veteran
   I need to be able to communicate with the newbs

   Scenario: Denial of Access
      Given I am logged out
      When I go to "/messages"
      Then I should see "You need to be signed in"

   Scenario: Banned user
      Given I am a banned user
      And there is a player called "allejo"
      When I go to "/messages"
      Then I should see "You are not allowed to send messages"
      When "allejo" sends me a message
      And I go to "/messages/1"
      And I fill in "composeArea" with "unban me pl0x"
      And I press "Send"
      Then I should see "You are not allowed to send messages"

   Scenario: Send new message errors
      Given I am logged in as "unequivocal"
      And there is a player called "Incontestable"
      When I go to "/messages"
      And I fill in "form_Recipients_players" with "INCONTESTABLE,instant"
      And I press "Send"
      Then I should see "There is no player called instant"
      But I should not see "There is no player called INCONTESTABLE"
      And I should see "This value should not be blank"
      When I fill in "form_Recipients_players" with " ,   unequivocal  ,,,   "
      And I press "Send"
      Then I should see "You can't send a message to yourself"

   Scenario: Send new message
     Given I am logged in
     And there is a player called "puissant"
     When I go to "/messages"
     And I fill in "form_Recipients_players" with "puissant"
     And I fill in "form_Subject" with "Importance"
     And I fill in "form_Message" with "Lorem ipsum text"
     And I press "Send"
     Then I should be on "/messages/2"
     And I should see "puissant"
     And I should see "Importance"
     And I should see "Lorem ipsum text"

   Scenario: Send reply
      Given I am logged in
      And there is a player called "Garrulous"
      And "Garrulous" sends me a message
      When I go to "/messages/3"
      Then I should see "Garrulous"
      When I fill in "composeArea" with "   "
      And I press "Send"
      Then I should see "You can't send an empty message"
      When I move backward one page
      And I fill in "composeArea" with "Hey"
      And I press "Send"
      Then I should see "Hey"
