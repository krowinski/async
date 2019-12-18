<?php
declare(strict_types=1);

namespace Async;

use RuntimeException;
use Symfony\Component\Process\Process;

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

    public function startJob(
        callable $callback = null,
        callable $onError = null
    ): void {
        $this->hasCallbackSet = null !== $callback;
        $this->hasOnErrorSet = null !== $callback;

        $this->start(
            static function (string $type, string $buffer) use ($callback, $onError) {
                // if we can't decode probably child failed to execute
                if ($decoded = base64_decode($buffer, true)) {
                    /** @var AsyncChildResponse $asyncChildResponse */
                    $asyncChildResponse = unserialize($decoded, [false]);
                } else {
                    throw new RuntimeException('Child process returned: ' . $buffer);
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

    public function hasCallbackSet(): bool
    {
        return $this->hasCallbackSet;
    }

    public function hasOnErrorSet(): bool
    {
        return $this->hasOnErrorSet;
    }
}