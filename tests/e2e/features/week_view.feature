# language: en
Feature: Admin Week View - Core habit tracking cycle
  As an administrator (Zootechnik)
  I want to see the weekly schedule and mark tasks as done
  So that the team's daily rhythm is tracked and verified

  Scenario: Admin can view the current week dashboard
    And I am on the admin week current page
    Then I should see "AgroFlow"

  Scenario: Admin can generate the current week
    And I am on the admin week current page
    And the current week has been generated
    Then I should see "Poniedziałek"

  Scenario: Admin can mark a task as done and it turns green
    And I am on the admin week current page
    And the current week has been generated
    When I click first pending task button
    Then I should see "Szczegóły Zadania"
    When I click the "Oznacz jako Wykonane" button
    Then the page should have a green task element
