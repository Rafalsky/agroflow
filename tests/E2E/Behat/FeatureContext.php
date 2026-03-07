<?php

declare(strict_types=1);

namespace App\Tests\E2E\Behat;

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\ORM\EntityManagerInterface;

/**
 * E2E Feature Context for browser-driven Behat tests.
 * Uses Panther (ChromeDriver) to run full browser scenarios.
 * Panther connects to the live dev server (127.0.0.1:8000) so
 * no explicit kernel/DI injection is needed here.
 */
class FeatureContext extends RawMinkContext implements Context
{
    private ?EntityManagerInterface $em = null;

    /**
     * Panther connects to the live dev server where the app runs in test env.
     * We don't have DI injection out of the box without SymfonyExtension kernel inject.
     * We'll use Mink to interact and if we need DB, we'll boot kernel manually or rely on UI setup steps.
     */
    public function __construct()
    {
    }

    /**
     * @BeforeScenario
     */
    public function setupDatabase(BeforeScenarioScope $scope): void
    {
        // Connect directly to the database the running dev server is using
        $dbUrl = $_ENV['DATABASE_URL'] ?? 'postgresql://app:!ChangeMe!@127.0.0.1:5432/app_test?serverVersion=16&charset=utf8';
        $dbConfig = parse_url($dbUrl);
        $dbname = ltrim($dbConfig['path'], '/');
        // By default, Symfony in test env often appends _test to the DB name from DATABASE_URL
        if (strpos($dbname, '_test') === false && isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'test') {
            $dbname .= '_test';
        }

        $dsn = "pgsql:host=127.0.0.1;port=5432;dbname={$dbname};";
        $pdo = new \PDO($dsn, "app", "!ChangeMe!", [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

        $tables = [
            'welfare_stock_change',
            'current_stock',
            'audit_log',
            'task_instance',
            'production_week',
            'task_template',
            'worker'
        ];

        foreach ($tables as $table) {
            try {
                $pdo->exec("TRUNCATE TABLE $table CASCADE");
            }
            catch (\PDOException $e) {
            // Ignore missing table errors
            }
        }

        // Insert one recurring task template on Monday (weekday 1) to test week generation
        $pdo->exec("INSERT INTO task_template (id, name, points, priority, weekday, recurring, active, instruction, widget_type) VALUES (999, 'Testowy Obchód', 10, 'NORMAL', 1, true, true, null, null)");

    }

    /**
     * @Given I am logged in as admin
     */
    public function iAmLoggedInAsAdmin(): void
    {
        $this->visitPath('/login');
        $page = $this->getSession()->getPage();
        $page->fillField('email', 'admin@agroflow.pl');
        $page->fillField('password', 'password');

        $btn = $page->findButton('Sign in') ?? $page->findButton('Zaloguj') ?? $page->find('css', 'button[type="submit"]');
        if ($btn) {
            $btn->click();
            $this->getSession()->wait(1500);
        }
        else {
            $form = $page->find('css', 'form');
            if ($form)
                $form->submit();
        }
    }

    /**
     * @Given I am on the admin week current page
     */
    public function iAmOnAdminWeekCurrentPage(): void
    {
        $this->visitPath('/admin');
    }

    /**
     * @Given the current week has been generated
     */
    public function theCurrentWeekHasBeenGenerated(): void
    {
        // If "Generuj Zadania" button exists, click it to create the week's tasks
        $page = $this->getSession()->getPage();
        $genBtn = $page->find('css', 'form[action*="generate"] button, button[form*="generate"]');
        $btn = $page->find('css', 'form[action*="generate"] button, button[form*="generate"], a[href*="generate"]');
        if (!$genBtn) {
            // If the generation button is not there, maybe the week is already generated. Let's not fail, but we wait 1s.
            $this->getSession()->wait(1000);
        }
        else {
            $genBtn->click();
            $this->getSession()->wait(3000, "document.body.innerText.includes('Poniedziałek')");
        }
    }

    // =========================================================================
    //  INTERACTION STEPS
    // =========================================================================

    /**
     * @When I click the :buttonText button
     */
    public function iClickTheButton(string $buttonText): void
    {
        $page = $this->getSession()->getPage();
        // Give the UI a moment (e.g. drawer opening animation) before looking
        $this->getSession()->wait(1000);

        $btn = $page->findButton($buttonText) ?? $page->findLink($buttonText);

        if (!$btn) {
            // Also try finding it by visible text instead of standard button locator
            // Using translated xpath for case-insensitivity on contains
            $btn = $page->find('xpath', "//*[contains(translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '" . strtolower($buttonText) . "')]");
        }

        if (!$btn) {
            file_put_contents('/tmp/behat-failure-btn.html', $page->getHtml());
            throw new \Exception("Button/link with text '{$buttonText}' not found on page. HTML written to /tmp/behat-failure-btn.html");
        }
        $btn->click();
        $this->getSession()->wait(1500);
    }

    /**
     * @When I click first pending task button
     */
    public function iClickFirstPendingTaskButton(): void
    {
        $page = $this->getSession()->getPage();
        $button = $page->find('css', 'div.group.cursor-pointer');
        if (!$button) {
            throw new \Exception("No task cards found on the page");
        }
        $button->click();
        $this->getSession()->wait(1500);
    }

    /**
     * @When I fill the number field with :value
     */
    public function iFillNumberFieldWith(string $value): void
    {
        $page = $this->getSession()->getPage();
        $input = $page->find('css', 'input[type="number"]');
        if (!$input) {
            throw new \Exception("Number input not found");
        }
        $input->setValue($value);
    }

    /**
     * @When I fill the textarea with :text
     */
    public function iFillTextareaWith(string $text): void
    {
        $page = $this->getSession()->getPage();
        $textarea = $page->find('css', 'textarea');
        if (!$textarea) {
            throw new \Exception("Textarea not found");
        }
        $textarea->setValue($text);
    }

    // =========================================================================
    //  ASSERTION STEPS
    // =========================================================================

    /**
     * @Then I should see :text
     */
    public function iShouldSee(string $text): void
    {
        // Wait up to 3 seconds for Vue to render the text
        $jsText = addslashes(mb_strtolower($text));
        $this->getSession()->wait(3000, "document.body.innerText.toLowerCase().includes('$jsText')");

        $actualText = $this->getSession()->getPage()->getText();
        if (mb_stripos($actualText, $text, 0, 'UTF-8') === false) {
            file_put_contents('/tmp/behat-failure.html', $this->getSession()->getPage()->getContent());
            throw new \Exception(sprintf("The text '%s' was not found anywhere in the visible text of the current page. Actual text: \n%s", $text, mb_substr($actualText, 0, 500)));
        }
    }

    /**
     * @Then I should not see :text
     */
    public function iShouldNotSee(string $text): void
    {
        $this->assertSession()->pageTextNotContains($text);
    }

    /**
     /**
     * @Then the page should not have a green task element
     */
    public function thePageShouldNotHaveGreenTaskElement(): void
    {
        $page = $this->getSession()->getPage();
        // Sprawdzamy czy nie ma elementów oznaczonych jako DONE (zielone)
        $greenElements = $page->findAll('css', '.bg-emerald-500, .text-emerald-600, .bg-emerald-50');

        if (count($greenElements) > 0) {
            throw new \Exception('Znaleziono zielony element zadania (DONE), a nie powinno go być.');
        }
    }

    /**
     * @Given there is a valid worker with access token :token and name :name
     */
    public function thereIsAValidWorkerWithAccessTokenAndName(string $token, string $name): void
    {
        $dbUrl = $_ENV['DATABASE_URL'] ?? 'postgresql://app:!ChangeMe!@127.0.0.1:5432/app_test?serverVersion=16&charset=utf8';
        $dbConfig = parse_url($dbUrl);
        $dbname = ltrim($dbConfig['path'] ?? 'app_test', '/');
        if (strpos($dbname, '_test') === false && isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'test') {
            $dbname .= '_test';
        }

        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s',
            $dbConfig['host'] ?? '127.0.0.1',
            $dbConfig['port'] ?? 5432,
            $dbname
        );
        $user = $dbConfig['user'] ?? 'app';
        $password = $dbConfig['pass'] ?? '';

        $pdo = new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

        $stmt = $pdo->prepare("SELECT id FROM worker WHERE access_token = :token");
        $stmt->execute(['token' => $token]);
        $worker = $stmt->fetch();

        if (!$worker) {
            $stmt = $pdo->prepare("INSERT INTO worker (name, short_name, active, access_token) VALUES (:name, :short_name, true, :token)");
            $stmt->execute([
                'name' => $name,
                'short_name' => substr($name, 0, 2),
                'token' => $token
            ]);
        }
    }

    /**
     * @Given I am on the worker entry page
     */
    public function iAmOnTheWorkerEntryPage(): void
    {
        $this->visitPath('/w/');
    }

    /**
     * @When I fill in :field with :value
     */
    public function iFillInWith(string $field, string $value): void
    {
        $page = $this->getSession()->getPage();
        // Spróbuj znaleźć po etykiecie
        $input = $page->findField($field);

        if (null === $input) {
            // Spróbuj znaleźć po nazwie (name)
            $input = $page->find('css', 'input[name="access_token"]');
        }

        if (null === $input) {
            throw new \Exception(sprintf('Nie znaleziono pola "%s"', $field));
        }

        $input->setValue($value);
    }

    /**
     * @When I press :buttonText
     */
    public function iPress(string $buttonText): void
    {
        // Reuse iClickTheButton which has robust waits and retries
        $this->iClickTheButton($buttonText);
    }

    /**
     * @Then I should be on the worker tasks page
     */
    public function iShouldBeOnTheWorkerTasksPage(): void
    {
        $this->getSession()->wait(2000, "window.location.pathname.startsWith('/worker/')");
        $currentUrl = $this->getSession()->getCurrentUrl();
        if (!str_contains($currentUrl, '/worker/tasks')) {
            throw new \Exception(sprintf('Oczekiwano strony "/worker/tasks", ale jesteśmy na "%s"', $currentUrl));
        }
    }

    /**
     * @Then the page should have a green task element
     */
    public function thePageShouldHaveGreenTaskElement(): void
    {
        $page = $this->getSession()->getPage();
        // The drawer status text uses .text-emerald-600, task button uses .bg-emerald-500, task card uses .border-emerald-100
        $green = $page->find('css', '.bg-emerald-500, .text-emerald-600, .border-emerald-100');
        if (!$green) {
            throw new \Exception("No green (DONE) task elements found – task may not have been completed");
        }
    }
}