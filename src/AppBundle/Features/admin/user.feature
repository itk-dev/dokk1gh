Feature: admin
  In order to …
  As a client …
  I need to be able to …

  Background:
    Given the following users exist:
      | email             | password | roles      |
      | admin@example.com | admin    | ROLE_ADMIN |

    Given the following "Template" entities exist:
      | name               | aeosId |
      | The first template |      1 |
      | Another template   |      2 |

  @createSchema
  Scenario: Create new user
    When I authenticate as "admin@example.com"
    And I go to "/"
    And I follow "Bruger"
    And I follow "Opret Bruger"
    Then I should be on url matching "/?action=new&entity=User"

    When I fill in "E-mail" with "user@example"
    And press "Gem"
    Then I should see "Indtast venligst en gyldig e-mailadresse" in the ".error-block" element

    When I fill in "E-mail" with "user@example.com"
    And press "Gem"
    Then I should see "Vælg venligst mindst ét adgangsområde" in the ".error-block" element

    When I check "The first template"
    And press "Gem"
    Then I should see "\"\" er ikke et gyldigt AEOS-person-id" in the ".error-block" element

    When I fill in "AEOS-id" with "1"
    And press "Gem"
    Then I should be on url matching "/?action=list&entity=User"
    And show last response
    And I should see "Mail om oprettelse sendt til user@example.com" in the ".alert-info" element

  Scenario: Set user password
    When I go to password create url for user "user@example.com"
    And press "Angiv adgangskode"
    Then I should see "Indtast venligst en adgangskode"

    When I fill in "Ny adgangskode" with "password for user@example.com"
    And press "Skift adgangskode"
    Then I should see "fos_user.password.mismatch"

    When I fill in "Ny adgangskode" with "password for user@example.com"
    And I fill in "Gentag adgangskode" with "password for user@example.com"
    And press "Skift adgangskode"
    Then I should be on "/login"
    And I should see "Adgangskoden er blevet nulstillet" in the ".alert-success" element

  Scenario: Sign in as user
    When I go to "/"
    Then I should be on "/login"

    When I fill in "E-mailadresse" with "user@example.com"
    And fill in "Adgangskode" with "admin"
    And press "Log ind"
    Then I should see "Ugyldige loginoplysninger" in the ".error-block" element

    When I fill in "E-mailadresse" with "user@example.com"
    And fill in "Adgangskode" with "password"
    And press "Log ind"
    Then I should see "Ugyldige loginoplysninger" in the ".error-block" element

    When I fill in "E-mailadresse" with "user@example.com"
    When I fill in "Adgangskode" with "password for user@example.com"
    And press "Log ind"
    Then I should be on url matching "/?action=list&entity=Code"

  @dropSchema
  Scenario: Drop schema
