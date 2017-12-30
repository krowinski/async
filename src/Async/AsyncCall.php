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
                try {

                    /** @var AsyncChildResponse $asyncChildResponse */
                    $asyncChildResponse = unserialize(base64_decode($buffer));

                    if (null !== $onError && $asyncChildResponse->getError()) {
                        $onError($asyncChildResponse->getError());
                    }

                    if (null !== $callback) {
                        $callback($asyncChildResponse->getJobResult());
                    }

                    if (null !== $asyncChildResponse->getOb()) {
                        echo $asyncChildResponse->getOb();
                    }
                } catch (\Exception $exception) {
                    $onError($exception);
                }
            }
        );

        //echo $process->getCommandLine() . PHP_EOL;

        self::$processList[] = $process;
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