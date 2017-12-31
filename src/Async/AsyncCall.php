<?php


namespace Async;

use SuperClosure\Serializer;
use Symfony\Component\Process\Process;

/**
 * Class AsyncCall
 * @package Async
 */
class AsyncCall
{
    const CONSOLE_EXECUTE = 'php ' . __DIR__ . '/../../bin/console app:run-child-process ';

    /**
     * @var bool
     */
    private static $shutdownFunctionRegistered = false;
    /**
     * @var Process[]
     */
    private static $processList = [];
    /**
     * @var Serializer
     */
    private static $serializer;

    /**
     * @param callable $job
     * @param callable $callback
     * @param callable $onError
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     */
    public static function run(callable $job, callable $callback = null, callable $onError = null)
    {
        self::registerShutdownFunction();

        if (!self::$serializer) {
            self::$serializer = new Serializer();
        }

        $jobEncoded = base64_encode(self::$serializer->serialize($job));

        $process = new Process(self::CONSOLE_EXECUTE . $jobEncoded);
        $process->start(
            function ($type, $buffer) use ($callback, $onError) {

                // if we can't decode probably child failed to execute
                if ($decoded = base64_decode($buffer, true)) {
                    /** @var AsyncChildResponse $asyncChildResponse */
                    $asyncChildResponse = unserialize($decoded);
                } else {
                    throw new \RuntimeException('Child process returned: ' . $buffer);
                }

                if (null !== $onError && $asyncChildResponse->getError()) {
                    $onError($asyncChildResponse->getError());
                }

                if (null !== $callback) {
                    $callback($asyncChildResponse->getJobResult());
                }

                if (null !== $asyncChildResponse->getOb()) {
                    echo $asyncChildResponse->getOb();
                }
            }
        );

        //echo $process->getCommandLine() . PHP_EOL;

        // register and wait for process that have callbacks
        if (null !== $callback) {
            self::$processList[] = $process;
        }
    }

    private static function registerShutdownFunction()
    {
        if (!self::$shutdownFunctionRegistered) {
            register_shutdown_function(
                function () {

                    while (true) {
                        if (0 === count(self::$processList)) {
                            break;
                        }
                        foreach (self::$processList as $i => $process) {
                            if ($process->getStatus() === Process::STATUS_TERMINATED) {
                                unset(self::$processList[$i]);
                                continue;
                            }
                        }
                    }
                }

            );
            self::$shutdownFunctionRegistered = true;
        }
    }
}