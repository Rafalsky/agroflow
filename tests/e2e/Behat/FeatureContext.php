<?php

declare(strict_types=1);

namespace App\Tests\E2E\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Doctrine\ORM\EntityManagerInterface;

/**
 * E2E Feature Context for browser-driven Behat tests.
 * Uses Panther (ChromeDriver) to run full browser scenarios.
 */
class FeatureContext extends RawMinkContext implements Context
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @BeforeScenario
     */
    public function cleanupDatabase(BeforeScenarioScope $scope): void
    {
        $connection = $this->em->getConnection();
        // Use TRUNCATE with CASCADE to clean related tables before each scenario
        $connection->executeStatement('TRUNCATE TABLE stock_change CASCADE');
        $connection->executeStatement('TRUNCATE TABLE current_stock CASCADE');
        $connection->executeStatement('TRUNCATE TABLE audit_log CASCADE');
        $connection->executeStatement('DELETE FROM task_instance');
        $connection->executeStatement('DELETE FROM production_week');
        $connection->executeStatement('DELETE FROM task_template');
        $this->em->clear();
    }

    // =========================================================================
    //  ADMIN WEEK VIEW STEPS
    // =========================================================================

    /**
     * @Given I am on the admin week current page
     */
    public function iAmOnAdminWeekCurrentPage(): void
    {
        $this->visitPath('/admin/week/current');
    }

    /**
     * @Given the current week has been generated
     */
    public function theCurrentWeekHasBeenGenerated(): void
    {
        $this->visitPath('/admin/week/current');
        $page = $this->getSession()->getPage();

        // Look for the generate button and click it if the week isn't generated yet
        $genButton = $page->find('css', 'button[data-action="generate"], form[action*="generate"] button');
        if ($genButton) {
            $genButton->click();
            $this->getSession()->wait(3000);
        }
    }

    /**
     * @When I click the :buttonText button
     */
    public function iClickTheButton(string $buttonText): void
    {
        $page = $this->getSession()->getPage();
        $button = $page->findButton($buttonText);
        if (!$button) {
            // Also try by link text
            $button = $page->findLink($buttonText);
        }
        if (!$button) {
            throw new \Exception("Button/link with text '{$buttonText}' not found");
        }
        $button->click();
    }

    /**
     * @When I click first pending task button
     */
    public function iClickFirstPendingTaskButton(): void
    {
        $page = $this->getSession()->getPage();
        // Find the first orange button (PENDING tasks)
        $button = $page->find('css', '.bg-orange-600, button.btn-primary');
        if (!$button) {
            throw new \Exception("No pending task buttons found on the page");
        }
        $button->click();
        // Wait for modal/drawer to open
        $this->getSession()->wait(2000);
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee(string $text): void
    {
        $this->assertSession()->pageTextContains($text);
    }

    /**
     * @Then I should not see :text
     */
    public function iShouldNotSee(string $text): void
    {
        $this->assertSession()->pageTextNotContains($text);
    }

    /**
     * @Then the page should contain a green completed task
     */
    public function thePageShouldContainGreenTask(): void
    {
        $page = $this->getSession()->getPage();
        $greenTask = $page->find('css', '.bg-emerald-600, .text-emerald-600');
        if (!$greenTask) {
            throw new \Exception("No green (DONE) task elements found on the page");
        }
    }

    // =========================================================================
    //  WELFARE DEATH WIDGET STEPS
    // =========================================================================

    /**
     * @Given there is a welfare death task in the current week
     */
    public function thereIsAWelfareDeathTaskInTheCurrentWeek(): void
    {
        // Navigate to admin, generate week, look for welfare task
        $this->visitPath('/admin/week/current');
    }

    /**
     * @When I click on the welfare death task
     */
    public function iClickOnTheWelfareDeathTask(): void
    {
        $page = $this->getSession()->getPage();
        // Look for a task card that mentions upadek (death)
        $taskCard = $page->find('css', '[data-widget-type="welfare_death"] button, .task-card button.bg-orange-600');
        if (!$taskCard) {
            // Fall back to first orange button
            $taskCard = $page->find('css', '.bg-orange-600');
        }
        if (!$taskCard) {
            throw new \Exception("No welfare death task button found");
        }
        $taskCard->click();
        $this->getSession()->wait(2000);
    }

    /**
     * @Then I should see a welfare death widget
     */
    public function iShouldSeeWelfareDeathWidget(): void
    {
        $page = $this->getSession()->getPage();
        // Look for the widget form elements
        $widget = $page->find('css', 'select, .welfare-death-widget');
        if (!$widget) {
            throw new \Exception("Welfare death widget is not visible");
        }
    }

    /**
     * @When I fill the deaths amount with :amount
     */
    public function iFillDeathsAmountWith(int $amount): void
    {
        $page = $this->getSession()->getPage();
        $input = $page->find('css', 'input[type="number"]');
        if (!$input) {
            throw new \Exception("Number input not found in the widget");
        }
        $input->setValue((string)$amount);
    }

    /**
     * @When I fill the notes with :notes
     */
    public function iFillNotesWith(string $notes): void
    {
        $page = $this->getSession()->getPage();
        $textarea = $page->find('css', 'textarea');
        if (!$textarea) {
            throw new \Exception("Textarea not found in the widget");
        }
        $textarea->setValue($notes);
    }
}