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
        if ($this->resource !== null && !is_resource($this->resource)) {
            $message = 'Invalid input custom resource argument.';
            throw new \InvalidArgumentException($message);
        }
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
     * Request the user to input yes or no.
     */
    public function askUntil(string $message, callable $callback): string
    {
        for (;;) {
            $input = $this->ask($message);
            if (call_user_func($callback, $input)) {
                return $input;
            }
        }
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
