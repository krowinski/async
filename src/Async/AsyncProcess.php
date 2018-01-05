<?php


namespace Async;

use Symfony\Component\Process\Process;

/**
 * Class AsyncProcess
 * @package Async
 */
class AsyncProcess extends Process
{
    /**
     * @var bool
     */
    private $hasCallbackSet;
    /**
     * @var bool
     */
    private $hasOnErrorSet;

    /**
     * @param callable|null $callback
     * @param callable|null $onError
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     */
    public function startJob(callable $callback = null, callable $onError = null)
    {
        $this->hasCallbackSet = null !== $callback;
        $this->hasOnErrorSet = null !== $callback;

        $this->start(
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
                    $callback($asyncChildResponse->getJobResult(), $asyncChildResponse->getStdOut());
                }
            }
        );
    }

    /**
     * @return bool
     */
    public function hasCallbackSet()
    {
        return $this->hasCallbackSet;
    }

    /**
     * @return bool
     */
    public function hasOnErrorSet()
    {
        return $this->hasOnErrorSet;
    }
}