<?php

namespace AppBundle\Command;

use AppBundle\Service\AeosService;
use AppBundle\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugCommand extends ContainerAwareCommand
{
    /** @var \Doctrine\ORM\EntityManagerInterface  */
    protected $entityManager;

    /** @var \AppBundle\Service\AeosService  */
    protected $aeosService;

    /** @var \AppBundle\Service\UserManager  */
    protected $userManager;

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    public function __construct(EntityManagerInterface $entityManager, AeosService $aeosService, UserManager $userManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->aeosService = $aeosService;
        $this->userManager = $userManager;
    }

    public function configure()
    {
        parent::configure();
        $this->setName('app:debug')
            ->addArgument('cmd', InputArgument::REQUIRED, 'The command to run')
            ->addArgument('arguments', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The arguments to the command')
            ->addHelp();
    }

    /**
     * @action Debug email sent to new user
     */
    private function notifyUserCreated($username)
    {
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (!$user) {
            throw new InvalidArgumentException('No such user: ' . $username);
        }

        $this->userManager->notifyUserCreated($user);

        $method = new \ReflectionMethod($this->userManager, 'createUserCreatedMessage');
        $method->setAccessible(true);
        /** @var \Swift_Message $message */
        $message = $method->invoke($this->userManager, $user);

        $this->output->writeln([
            str_repeat('=', 80),
            $message,
            str_repeat('-', 80),
            $message->getBody(),
            str_repeat('=', 80),
        ]);
    }

    /**
     * @action Debug email sent to new user
     */
    private function resetPassword($username)
    {
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (!$user) {
            throw new InvalidArgumentException('No such user: ' . $username);
        }

        $this->getContainer()->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->getContainer()->get('fos_user.user_manager')->updateUser($user);
    }

    private function addHelp()
    {
        $reflector = $reflector = new \ReflectionObject($this);

        $methods = $reflector->getMethods();

        $commands = array_map(function (\ReflectionMethod $method) {
            return $this->camel2kebab($method->getName());
        }, array_filter($methods, function (\ReflectionMethod $method) {
            return strpos($method->getDocComment(), '@action') !== false;
        }));

        if ($commands) {
            $this->setHelp('Commands: ' . PHP_EOL . implode(PHP_EOL, $commands));
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->getContainer()->get('kernel')->getEnvironment() !== 'dev') {
            throw new RuntimeException('Command only available in "dev" environment.');
        }

        $this->input = $input;
        $this->output = $output;

        $cmd = $this->input->getArgument('cmd');

        // kebab-case -> camelCase
        $method = $this->kebab2camel($cmd);
        if (method_exists($this, $method)) {
            $arguments = $this->input->getArgument('arguments');
            call_user_func_array([$this, $method], $arguments);
        } else {
            throw new CommandNotFoundException('Invalid command: ' . $cmd);
        }
    }

    private function camel2kebab($s)
    {
        return preg_replace_callback('/[A-Z]/', function ($matches) {
            return '-' . strtolower($matches[0]);
        }, $s);
    }

    private function kebab2camel($s)
    {
        return preg_replace_callback('/-([a-z0-9])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $s);
    }
}
