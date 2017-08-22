<?php

namespace AppBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behatch\Context\BaseContext;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends BaseContext implements Context, KernelAwareContext
{
    /** @var KernelInterface */
    private $kernel;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    private $tokenStorage;

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
    public function __construct(ManagerRegistry $doctrine, TokenStorageInterface $tokenStorage)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
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
     * @Given /^I am authenticated as "([^"]*)"$/
     *
     * @param mixed $username
     */
    public function iAmAuthenticatedAs($username)
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof BrowserKitDriver) {
            throw new UnsupportedDriverActionException('This step is only supported by the BrowserKitDriver');
        }

        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        $client = $driver->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $session = $client->getContainer()->get('session');
        $providerKey = 'primary_auth'; // @TODO: Get this from request and firewall configuration.

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_'.$providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
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
