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
    private $ob;
    /**
     * @var \Exception
     */
    private $error;

    /**
     * AsyncChildResponse constructor.
     * @param mixed $jobResult
     * @param string $ob
     * @param \Exception $error
     */
    public function __construct($jobResult, $ob = null, \Exception $error = null)
    {
        $this->jobResult = $jobResult;
        $this->ob = $ob;
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
    public function getOb()
    {
        return $this->ob;
    }

    /**
     * @return \Exception
     */
    public function getError()
    {
        return $this->error;
    }
}