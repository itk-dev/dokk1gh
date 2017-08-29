<?php

namespace AppBundle\Features\Context;

use AppBundle\Entity\Template;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behatch\Context\BaseContext;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @When /^I authenticate as "([^"]*)"$/
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
     * @When I fill in :field with datetime :datetime
     *
     * @param mixed $field
     * @param mixed $time
     * @param mixed $format
     */
    public function iFillInWithDatetime($field, $time, $format = \DateTime::W3C)
    {
        $date = new \DateTime($time, new \DateTimeZone('utc'));
        $context = $this->getMinkContext();
        $context->fillField($field, $date->format($format));
    }

    /**
     * @When I fill in :field with date :datetime
     *
     * @param mixed $field
     * @param mixed $time
     */
    public function iFillInWithDate($field, $time)
    {
        $this->iFillInWithDatetime($field, $time, 'Y-m-d');
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
     * @When I go to password :action url for user :email
     *
     * @param mixed $action
     * @param mixed $email
     */
    public function iGoToPasswordResetUrlForUser($action, $email)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByEmail($email);
        if (!$user) {
            throw new \RuntimeException('No such user: '.$email);
        }

        $path = $this->container->get('router')->generate('fos_user_resetting_reset', ['token' => $user->getConfirmationToken(), 'create' => $action === 'create']);
        $this->visitPath($path);
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

            $this->createUser($email, $password, $roles, $row);
        }
    }

    /**
     * @Given the following :type entities exist:
     *
     * @param mixed $type
     */
    public function theFollowingEntitiesExist($type, TableNode $table)
    {
        $class = 'AppBundle\\Entity\\'.$type;
        if (!class_exists($class)) {
            throw new \RuntimeException('Class '.$class.' does not exist.');
        }

        $accessor = $this->container->get('property_accessor');
        foreach ($table->getHash() as $row) {
            $entity = new $class();
            foreach ($row as $path => $value) {
                $accessor->setValue($entity, $path, $value);
            }
            $this->manager->persist($entity);
        }
        $this->manager->flush();
    }

    private function createUser(string $email, string $password, array $roles, $data = [])
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

        if (isset($data['templates'])) {
            $ids = preg_split('/\s*,\s*/', $data['templates'], -1, PREG_SPLIT_NO_EMPTY);
            $templates = $this->manager->getRepository(Template::class)->findBy(['id' => $ids]);
            $user->setTemplates(new ArrayCollection($templates));
        }
        if (isset($data['aeosId'])) {
            $user->setAeosId($data['aeosId']);
        }

        $userManager->updateUser($user);
    }
}
