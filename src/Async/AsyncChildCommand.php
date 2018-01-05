<?php


namespace Async;


use SuperClosure\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AsyncChildCommand
 * @package Async
 */
class AsyncChildCommand extends Command
{
    const PARAM_NAME_JOB = 'job';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * AsyncChildCommand constructor.
     * @param null $name
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->serializer = new Serializer();
    }

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('app:run-child-process')
            ->setDescription('Runs a child process.')
            ->addArgument(self::PARAM_NAME_JOB, InputArgument::REQUIRED, 'Serialized callback job param.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \SuperClosure\Exception\ClosureUnserializationException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $job = $this->serializer->unserialize(base64_decode($input->getArgument(self::PARAM_NAME_JOB)));

            ob_start();
            $jobResults = $job();
            $ob = ob_get_clean();
            $error = null;
        } catch (\Exception $exception) {
            $jobResults = null;
            $ob = null;
            $error = $exception;
        }

        $output->writeln(base64_encode(serialize(new AsyncChildResponse($jobResults, $ob, $error))));

        return 0;
    }
}