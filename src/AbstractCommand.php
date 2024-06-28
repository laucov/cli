<?php

namespace Laucov\Cli;

use Laucov\Cli\Interfaces\CommandInterface;

/**
 * Stores a command's information and procedures.
 */
abstract class AbstractCommand implements CommandInterface
{
    /**
     * Executes the command procedures.
     */
    abstract public function run(): void;

    /**
     * CLI printer instance.
     */
    protected Printer $printer;

    /**
     * Create the command instance.
     */
    public function __construct(
        /**
         * Request being handled.
         */
        protected AbstractRequest $request,
    ) {
        $this->printer = new Printer();
    }
}
