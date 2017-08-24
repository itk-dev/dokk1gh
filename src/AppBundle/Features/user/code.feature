Feature: Create code
  In order to …
  As a client …
  I need to be able to …

  Background:
    Given the following "Template" entities exist:
      | name               | aeosId |
      | The first template |      1 |
      | Another template   |      2  |

    And the following users exist:
      | email            | password | roles     | aeosId | templates |
      | user@example.com | password | ROLE_USER |      1 | 1,2       |

    And I authenticate as "user@example.com"

  @createSchema
  Scenario: Create code
    And I go to "/?action=new&entity=Code"
    And I fill in "code[startTime]" with "2017-08-24T08:00:00+02:00"
    And I fill in "code[endTime]" with "2017-08-01T16:00:00+02:00"
    And I fill in "Intern note" with "Behat test"
    And I press "Gem"
    Then I should see "Sluttidspunktet skal være efter starttidspunktet" in the ".error-block" element

    When I fill in "code[endTime]" with "2017-08-24T16:00:00+02:00"
    And I press "Gem"
    Then I should be on url matching "/?action=list&entity=Code"
    And I should see "2017-08-24 08:00–16:00"

  Scenario: Create code spanning multiple days
    When I go to "/?action=list&entity=Code"
    And I follow "Opret Kode"
    And I fill in "code[startTime]" with "2017-08-25T10:00:00+02:00"
    And I fill in "code[endTime]" with "2017-08-26T14:00:00+02:00"
    And I fill in "Intern note" with "Behat test"
    And I press "Gem"
    Then I should be on url matching "/?action=list&entity=Code"
    And I should see "2017-08-25 10:00–2017-08-26 14:00"

  @dropSchema
  Scenario: Drop schema
