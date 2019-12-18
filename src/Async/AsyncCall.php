<?php
declare(strict_types=1);

namespace Async;

use SuperClosure\Serializer;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class AsyncCall
{
    private const CONSOLE_LOCATION = __DIR__ . '/../../bin/console';

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
    private static $processAmount = 0;

    public static function setProcessLimit(int $processesLimit): void
    {
        if ($processesLimit < 0) {
            throw new InvalidArgumentException('Processes limit Must be positive integer');
        }
        self::$processesLimit = $processesLimit;
    }

    public static function run(
        callable $job,
        callable $callback = null,
        callable $onError = null,
        float $timeout = null,
        float $idleTimeout = null
    ): void {
        self::registerShutdownFunction();

        if (!self::$serializer) {
            self::$serializer = new Serializer();
        }

        // we got process limit so wait for them to finish
        if (0 !== self::$processesLimit && self::$processesLimit >= self::$processAmount) {
            self::waitForProcessesToFinish(self::$processesLimit);
        }

        $process = new AsyncProcess(
            [
                self::CONSOLE_LOCATION,
                AsyncChildCommand::COMMAND_NAME,
                base64_encode(self::$serializer->serialize($job))
            ]
        );
        $process->setTimeout($timeout);
        $process->setIdleTimeout($idleTimeout);
        $process->startJob($callback, $onError);

        self::$processList[] = $process;
        self::$processAmount++;
    }

    private static function registerShutdownFunction(): void
    {
        if (!self::$shutdownFunctionRegistered) {
            register_shutdown_function(
                static function () {
                    self::waitForProcessesToFinish();
                }
            );
            self::$shutdownFunctionRegistered = true;
        }
    }

    private static function waitForProcessesToFinish(int $maxProcessToWait = 0): void
    {
        for (; ;) {
            if (0 === self::$processAmount || $maxProcessToWait > self::$processAmount) {
                break;
            }

            foreach (self::$processList as $i => $process) {
                if (
                    $process->getStatus() === AsyncProcess::STATUS_TERMINATED ||
                    (!$process->hasCallbackSet() && !$process->hasOnErrorSet())
                ) {
                    unset(self::$processList[$i]);
                    self::$processAmount--;

                    continue;
                }
            }
        }
    }
}