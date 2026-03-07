Feature: Reporting animal deaths (PIWET)
  As per PIWET requirements and Stage 4 Welfare
  As a Worker I want to quickly report animal deaths during daily rounds
  So that the herd stock remains accurate

  Scenario: Reporting a death from the task drawer
    # 1. Accessing task via Worker UI
    Given I am logged in as a worker
    And there is a welfare death task in the current week for this worker
    When I click on the welfare death task
    Then I should see a welfare death widget

    # 2. Filling the widget
    When I select the age category "fatteners"
    And I fill the deaths amount with 2
    And I fill the notes with "Nagły upadek w komorze 3"
    And I click the "Oznacz jako Wykonane" button
    
    # 3. Verification
    Then the page should contain a green completed task
    And the stock for "fatteners" should be reduced by 2
    And the audit logs should contain a "DEATH" entry
