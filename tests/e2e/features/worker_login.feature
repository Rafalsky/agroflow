Feature: Worker Entry Portal
  As a worker
  I want to enter my access token on the entry portal
  So that I can see and manage my assigned tasks

  Scenario: Worker can login using the entry portal using their access token
    Given there is a valid worker with access token "TEST_TOKEN_123" and name "Jan Testowy"
    And I am on the worker entry page
    Then I should see "Wejście Pracownika"
    When I fill in "Twój Kod Dostępu" with "TEST_TOKEN_123"
    And I press "Wejdź do systemu"
    Then I should be on the worker tasks page
    And I should see "Jan Testowy"
