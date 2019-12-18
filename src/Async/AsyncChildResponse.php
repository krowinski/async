<?php
declare(strict_types=1);

namespace Async;

use Exception;

class AsyncChildResponse
{
    private $jobResult;
    private $stdout;
    private $error;

    public function __construct(
        $jobResult,
        ?string $stdout = null,
        ?Exception $error = null
    ) {
        $this->jobResult = $jobResult;
        $this->stdout = $stdout;
        $this->error = $error;
    }

    public function getJobResult()
    {
        return $this->jobResult;
    }

    public function getStdOut(): ?string
    {
        return $this->stdout;
    }

    public function getError(): ?Exception
    {
        return $this->error;
    }
}