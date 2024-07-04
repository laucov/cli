<?php

declare(strict_types=1);

namespace Tests;

use Laucov\Cli\Input;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Cli\Input
 */
final class InputTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::ask
     * @covers ::readLine
     */
    public function testCanReadInput(): void
    {
        // Setup input.
        $fp = $this->createInputResource(<<<TXT
            do-something --foo --bar
            John
            TXT);

        // Setup expectations.
        $this->expectOutputString(
            'You commanded "do-something --foo --bar".'
                . 'Insert your name: Your name is "John".'
        );

        // Test input.
        $input = new Input($fp);
        printf('You commanded "%s".', $input->readLine());
        printf('Your name is "%s".', $input->ask('Insert your name:'));
    }

    /**
     * Create an input file pointer from text lines.
     * 
     * @return resource
     */
    protected function createInputResource(string ...$lines): mixed
    {
        $data = base64_encode(implode("\n", $lines));
        $filename = "data://text/plain;base64,{$data}";
        return fopen($filename, 'r');
    }
}
