Feature: admin
  In order to …
  As a client …
  I need to be able to …

  Background:
    Given the following users exist:
      | email             | password | roles      |
      | admin@example.com | admin    | ROLE_ADMIN |

  @createSchema
  Scenario: Login
    When I go to "/"
    Then I should be on "/login"

    When I fill in "E-mailadresse" with "admin@example.com"
    And fill in "Adgangskode" with "admin"
    And press "Log ind"
    Then I should be on "/"
    Then I should be on url matching "/?action=list&entity=Code"

    When I follow "Kode"
    Then I should be on url matching "/?action=list&entity=Code"

    When I follow "Bruger"
    Then I should be on url matching "/?action=list&entity=User"

    When I follow "Adgangsområde"
    Then I should be on url matching "/?action=list&entity=Template"

    And I should see "Adgangsområde"
    And I should see "Opret Adgangsområde"

    When I follow "Log ud"
    Then I should be on "/login"

    When I fill in "E-mailadresse" with "admin@example.com"
    And fill in "Adgangskode" with "admin"
    And press "Log ind"
    Then I should be on "/"

    When I follow "Adgangsområde"
    And I follow "Opret Adgangsområde"
    And I fill in "Navn" with "The first template"
    And press "Gem"
    Then I should see "\"\" er ikke et gyldigt AEOS-skabelon-id"

    When I fill in "AEOS-id" with "12345678"
    And press "Gem"
    Then I should see "\"12345678\" er ikke et gyldigt AEOS-skabelon-id"

    When I fill in "AEOS-id" with "1"
    And press "Gem"
    Then I should be on url matching "/?action=list&entity=Template"

    When I follow "Bruger"
    And I follow "Rediger"
    Then the "E-mailadresse" field should contain "admin@example.com"
    And the "Administrator" checkbox should be checked
    And the "Bruger" checkbox should be checked

    When I check "The first template"
    And fill in "AEOS-id" with ""
    And press "Gem"
    Then I should see "\"\" er ikke et gyldigt AEOS-person-id" in the ".error-block" element

    And fill in "AEOS-id" with "87654321"
    And press "Gem"
    Then I should see "\"87654321\" er ikke et gyldigt AEOS-person-id" in the ".error-block" element

    And fill in "AEOS-id" with "1"
    And press "Gem"
    Then I should be on url matching "/?action=list&entity=User"

    When I follow "Skift adgangskode"
    Then I should be on "/profile/change-password"

    When I fill in "Nuværende adgangskode" with "admin"
    And fill in "Ny adgangskode" with "my-new-password"
    And fill in "Gentag adgangskode" with "my-new-password"
    And press "Skift adgangskode"
    Then I should be on url matching "/?action=list&entity=Code"
    And I should see "Adgangskoden er blevet opdateret" in the ".alert-success" element

    When I follow "Kode"
    And I follow "Opret Kode"
    And I fill in "Dato" with date "tomorrow"
    And I fill in "code[startTime]" with datetime "today +10 hours"
    And I fill in "code[endTime]" with datetime "today +16 hours"
    And I fill in "Intern note" with "A note for my first code"
    And press "Gem"
    Then I should be on url matching "/?action=list&entity=Code"
    And I should see "Ny kode oprettet:" in the ".alert-success" element
    And I should see "A note for my first code"

  @dropSchema
  Scenario: Drop schema
