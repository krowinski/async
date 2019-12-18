<?php
declare(strict_types=1);

namespace Async;

use Exception;
use SuperClosure\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AsyncChildCommand extends Command
{
    public const COMMAND_NAME = 'app:run-child-process';
    private const PARAM_NAME_JOB = 'job';
    private $serializer;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->serializer = new Serializer();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $job = $this->serializer->unserialize(base64_decode($input->getArgument(self::PARAM_NAME_JOB)));

            ob_start();
            $jobResults = $job();
            $ob = ob_get_clean();
            $error = null;
        } catch (Exception $exception) {
            $jobResults = null;
            $ob = null;
            $error = $exception;
        }

        $output->writeln(base64_encode(serialize(new AsyncChildResponse($jobResults, $ob, $error))));

        return 0;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Runs a child process.')
            ->addArgument(self::PARAM_NAME_JOB, InputArgument::REQUIRED, 'Serialized callback job param.');
    }
}