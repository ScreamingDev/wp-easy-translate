<?php

namespace WpEasyTranslate\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param string $message
     */
    public function verbose($message)
    {
        if (false === OutputInterface::VERBOSITY_VERBOSE & $this->getOutput()->getVerbosity()) {
            return;
        }

        $this->getOutput()->write($message);
    }

    /**
     * @return OutputInterface
     */
    protected function getOutput()
    {
        return $this->output;
    }

    /**
     * @param mixed $output
     */
    protected function setOutput($output)
    {
        $this->output = $output;
    }

    protected function debug($string)
    {
        if (false === OutputInterface::VERBOSITY_DEBUG & $this->getOutput()->getVerbosity()) {
            return;
        }

        $this->getOutput()->write('  '.$string);
    }

    /**
     * @return InputInterface
     */
    protected function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     */
    protected function setInput($input)
    {
        $this->input = $input;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->setOutput($output);
        $this->setInput($input);
    }
}