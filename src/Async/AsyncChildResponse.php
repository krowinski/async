<?php


namespace Async;

/**
 * Class AsyncChildResponse
 * @package Async
 */
class AsyncChildResponse
{
    /**
     * @var mixed
     */
    private $jobResult;
    /**
     * @var string
     */
    private $stdout;
    /**
     * @var \Exception
     */
    private $error;

    /**
     * AsyncChildResponse constructor.
     * @param mixed $jobResult
     * @param string $stdout
     * @param \Exception $error
     */
    public function __construct($jobResult, $stdout = null, \Exception $error = null)
    {
        $this->jobResult = $jobResult;
        $this->stdout = $stdout;
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getJobResult()
    {
        return $this->jobResult;
    }

    /**
     * @return string
     */
    public function getStdOut()
    {
        return $this->stdout;
    }

    /**
     * @return \Exception
     */
    public function getError()
    {
        return $this->error;
    }
}