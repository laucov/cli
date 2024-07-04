<?php

namespace Laucov\Cli;

use Laucov\Validation\Error;
use Laucov\Validation\Ruleset;

/**
 * Reads user input.
 */
class Input
{
    /**
     * Create the input instance.
     */
    public function __construct(
        /**
         * Input file pointer.
         * 
         * @var resource
         */
        protected mixed $resource,
    ) {
    }

    /**
     * Request a single user input line.
     */
    public function ask(string $message): string
    {
        echo $message . ' ';
        return $this->readLine();
    }

    /**
     * Read the next user input line.
     */
    public function readLine(): string
    {
        $line = fgets($this->resource);
        return trim($line);
    }
}
