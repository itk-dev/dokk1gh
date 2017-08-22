<?php

namespace AppBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behatch\Context\BaseContext;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends BaseContext implements Context, KernelAwareContext
{
    private $kernel;
    private $container;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $manager;

    private $request;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->classes = $this->manager->getMetadataFactory()->getAllMetadata();
    }

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->container = $this->kernel->getContainer();
    }

    /**
     * @BeforeScenario @createSchema
     */
    public function createDatabase()
    {
        $this->schemaTool->createSchema($this->classes);
    }

    /**
     * @AfterScenario @dropSchema
     */
    public function dropDatabase()
    {
        $this->schemaTool->dropSchema($this->classes);
    }

    /**
     * @Then I should be on url matching :pathAndQuery
     *
     * @param mixed $pathAndQuery
     */
    public function iShouldBeOnUrlMatching($pathAndQuery)
    {
        $expectedUrl = parse_url($pathAndQuery);
        $actualUrl = parse_url($this->getSession()->getCurrentUrl());

        $this->assertSame($expectedUrl['path'], $actualUrl['path'], 'Url paths do not match');

        parse_str($expectedUrl['query'], $expectedQuery);
        parse_str($actualUrl['query'], $actualQuery);

        foreach ($expectedQuery as $name => $value) {
            $this->assertArrayHasKey($name, $actualQuery);
            $this->assertSame($expectedQuery[$name], $actualQuery[$name]);
        }
    }

    /**
     * @Given the following users exist:
     */
    public function theFollowingUsersExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $email = $row['email'];
            $password = isset($row['password']) ? $row['password'] : uniqid();
            $roles = isset($row['roles']) ? preg_split('/\s*,\s*/', $row['roles'], -1, PREG_SPLIT_NO_EMPTY) : [];

            $this->createUser($email, $password, $roles);
        }
    }

    private function createUser(string $email, string $password, array $roles)
    {
        /** @var \AppBundle\Service\UserManager $userManager */
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->findUserByEmail($email);
        if (!$user) {
            $user = $userManager->createUser();
        }
        $user
      ->setEnabled(true)
      ->setUsername($email)
      ->setPlainPassword($password)
      ->setEmail($email)
      ->setRoles($roles);
        $userManager->updateUser($user);
    }
}
