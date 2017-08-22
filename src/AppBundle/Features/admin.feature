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

    # When I fill in "security.login.username" with "admin@example.com"
    # And fill in "security.login.password" with "admin"
    # And press "security.login.submit"
    # Then I should be on "/"
    # Then I should be on url matching "/?action=list&entity=Code"

    # When I follow "Code"
    # Then I should be on url matching "/?action=list&entity=Code"

    # When I follow "User"
    # Then I should be on url matching "/?action=list&entity=User"

    # When I follow "Template"
    # Then I should be on url matching "/?action=list&entity=Template"

    # And I should see "Template"
    # And I should see "action.new"

    #    When I follow "user.signout"
    #    Then I should be on "/login"

       When I fill in "security.login.username" with "admin@example.com"
       And fill in "security.login.password" with "admin"
       And press "security.login.submit"
       Then I should be on "/"

       When I follow "Template"
       And I follow "action.new"
       And I fill in "Name" with "The first template"
       And press "action.save"
       Then I should see "\"\" is not a valid AEOS template id."

       When I fill in "Aeos id" with "12345678"
       And press "action.save"
       Then I should see "\"12345678\" is not a valid AEOS template id."

       When I fill in "Aeos id" with "1"
       And press "action.save"
       Then I should be on url matching "/?action=list&entity=Template"

       When I follow "User"
       And I follow "action.edit"
       Then the "Email" field should contain "admin@example.com"
       And the "ROLE_ADMIN" checkbox should be checked
       And the "ROLE_USER" checkbox should be checked

       When I check "The first template"
       And fill in "Aeos id" with ""
       And press "action.save"
       Then I should see "\"\" is not a valid AEOS person id." in the ".error-block" element

       And fill in "Aeos id" with "87654321"
       And press "action.save"
       Then I should see "\"87654321\" is not a valid AEOS person id." in the ".error-block" element

       And fill in "Aeos id" with "1"
       And press "action.save"
       Then I should be on url matching "/?action=list&entity=User"

       When I follow "Change password"
       Then I should be on "/profile/change-password"

       When I fill in "form.current_password" with "admin"
       And fill in "form.new_password" with "my-new-password"
       And fill in "form.new_password_confirmation" with "my-new-password"
       And press "change_password.submit"
       Then I should be on url matching "/?action=list&entity=Code"
       And I should see "change_password.flash.success" in the ".alert-success" element

       When I follow "Code"
       And I follow "action.new"
       And I fill in "Note" with "A note for my first code"
       And press "action.save"
       Then I should be on url matching "/?action=list&entity=Code"
       And I should see "New code created:" in the ".alert-success" element
       And I should see "A note for my first code"

       # And show last response

  @dropSchema
  Scenario: Drop schema
