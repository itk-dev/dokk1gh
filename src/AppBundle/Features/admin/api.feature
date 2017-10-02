Feature: API
  In order to …
  As a …
  I need to be able to …

  Background:
    Given the following users exist:
      | email             | apikey | roles      |
      | admin@example.com | admin  | ROLE_ADMIN |

  @createSchema
  Scenario: List templates
    When I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/api/admin/templates?apikey=admin"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/json"
    And the json node "" should have 25 elements

    When I send a "GET" request to "/api/admin/templates?apikey=admin&amount=10"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/json"
    And the json node "" should have 10 elements

  Scenario: List people
    When I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/api/admin/people?apikey=admin"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/json"
    And the json node "" should have 25 elements

    When I send a "GET" request to "/api/admin/people?apikey=admin&amount=10"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/json"
    And the json node "" should have 10 elements

  @dropSchema
  Scenario: Drop schema
