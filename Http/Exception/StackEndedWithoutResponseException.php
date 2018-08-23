<?php

declare(strict_types=1);

namespace Peak\Bedrock\Http\Exception;

use Peak\Blueprint\Http\Stack;

/**
 * Class StackEndedWithoutResponseException
 * @package Peak\Bedrock\Http\Exception
 */
class StackEndedWithoutResponseException extends \LogicException
{
    /**
     * @var Stack
     */
    private $stack;

    /**
     * StackEndedWithoutResponseException constructor.
     *
     * @param Stack $stack
     */
    public function __construct(Stack $stack)
    {
        parent::__construct('Stack ended without returning a response');
        $this->stack = $stack;
    }

    /**
     * @return Stack
     */
    public function getStack()
    {
        return $this->stack;
    }
}
