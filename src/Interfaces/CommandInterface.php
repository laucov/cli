<?php

namespace Laucov\Cli\Interfaces;

/**
 * Stores a command's information and procedures.
 */
interface CommandInterface
{
    /**
     * Executes the command procedures.
     */
    public function run(): void;
}
