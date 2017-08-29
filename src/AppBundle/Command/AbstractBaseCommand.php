<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Reflection\DocBlock\Tag\ParamTag;
use Zend\Code\Reflection\DocBlockReflection;

abstract class AbstractBaseCommand extends ContainerAwareCommand
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    protected $entityManager;

    /** @var \AppBundle\Service\AeosService */
    protected $aeosService;

    /** @var \AppBundle\Service\UserManager */
    protected $userManager;

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    public function configure()
    {
        parent::configure();
        $this
            ->addArgument('cmd', InputArgument::REQUIRED, 'The command to run')
            ->addArgument('arguments', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The arguments to the command')
            ->addHelp();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $cmd = $this->input->getArgument('cmd');

        $method = $this->kebab2camel($cmd);
        if (method_exists($this, $method)) {
            $arguments = $this->input->getArgument('arguments');
            call_user_func_array([$this, $method], $arguments);
        } else {
            throw new CommandNotFoundException('Invalid command: '.$cmd);
        }
    }

    private function addHelp()
    {
        $reflector = $reflector = new \ReflectionObject($this);

        $methods = $reflector->getMethods();

        $methods = array_filter($methods, function (\ReflectionMethod $method) {
            return preg_match('/@(action|command)/', $method->getDocComment());
        });

        if ($methods) {
            $items = [];
            $maxCommandNameLength = max(array_map('strlen', array_map(function ($method) {
                return $this->camel2kebab($method->getName());
            }, $methods)));

            foreach ($methods as $method) {
                $items[] = $this->renderMethodHelp($method, $maxCommandNameLength, '  ');
            }

            $this->setHelp('Commands: '.PHP_EOL.implode(PHP_EOL, $items));
        }
    }

    private function renderMethodHelp(\ReflectionMethod $method, int $commandNameWidth, string $indent = '')
    {
        $doc = new DocBlockReflection($method);
        $help = $indent.'<info>'.str_pad($this->camel2kebab($method->getName()), $commandNameWidth + 2).'</info>'.$doc->getShortDescription();
        if ($doc->getTags('param')) {
            $params = $doc->getTags('param');
            $paramNameWidth = max(array_map('strlen', array_map(function (ParamTag $param) {
                return $param->getVariableName();
            }, $params)));
            $help .= PHP_EOL.str_repeat($indent, 2).'Parameters:'.PHP_EOL;
            foreach ($params as $param) {
                $help .= $this->renderMethodParam($param, $paramNameWidth, str_repeat($indent, 3));
            }
        }

        return $help;
    }

    private function renderMethodParam(ParamTag $param, int $width, $indent = '')
    {
        return $indent.str_pad($param->getVariableName(), $width + 2).$param->getDescription().PHP_EOL;
    }

    private function camel2kebab($s)
    {
        return preg_replace_callback('/[A-Z]/', function ($matches) {
            return '-'.strtolower($matches[0]);
        }, $s);
    }

    private function kebab2camel($s)
    {
        return preg_replace_callback('/-([a-z0-9])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $s);
    }
}
