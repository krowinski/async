<?php


namespace Async;

use SuperClosure\Serializer;
use Symfony\Component\Console\Exception\InvalidArgumentException;

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
     * @var AsyncProcess[]
     */
    private static $processList = [];
    /**
     * @var Serializer
     */
    private static $serializer;
    /**
     * @var int
     */
    private static $processesLimit = 0;

    /**
     * @param $processesLimit
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public static function setProcessLimit($processesLimit)
    {
        if ($processesLimit < 0) {
            throw new InvalidArgumentException('Processes limit Must be possitive itiger');
        }
        self::$processesLimit = (int)$processesLimit;
    }

    /**
     * @param callable $job
     * @param callable $callback
     * @param callable $onError
     * @param float $timeout
     * @param float $idleTimeout
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \RuntimeException
     */
    public static function run(
        callable $job,
        callable $callback = null,
        callable $onError = null,
        $timeout = null,
        $idleTimeout = null
    ) {
        self::registerShutdownFunction();

        if (!self::$serializer) {
            self::$serializer = new Serializer();
        }

        // we got process limit so wait for them to finish
        if (0 !== self::$processesLimit && self::$processesLimit >= count(self::$processList)) {
            self::waitForProcessesToFinish(self::$processesLimit);
        }

        $process = new AsyncProcess(self::CONSOLE_EXECUTE . base64_encode(self::$serializer->serialize($job)));
        $process->setTimeout($timeout);
        $process->setIdleTimeout($idleTimeout);
        $process->startJob($callback, $onError);

        //echo $process->getCommandLine() . PHP_EOL;
        self::$processList[] = $process;
    }

    private static function registerShutdownFunction()
    {
        if (!self::$shutdownFunctionRegistered) {
            register_shutdown_function(
                function () {
                    self::waitForProcessesToFinish();
                }
            );
            self::$shutdownFunctionRegistered = true;
        }
    }

    /**
     * @param int $maxProcessToWait
     */
    private static function waitForProcessesToFinish($maxProcessToWait = 0)
    {
        while (true) {
            $processAmount = count(self::$processList);

            if (0 === $processAmount) {
                break;
            }
            if ($maxProcessToWait > $processAmount) {
                break;
            }

            foreach (self::$processList as $i => $process) {
                if ($process->getStatus() === AsyncProcess::STATUS_TERMINATED || (!$process->hasCallbackSet() && !$process->hasOnErrorSet())) {
                    unset(self::$processList[$i]);
                    continue;
                }
            }
        }
    }
}