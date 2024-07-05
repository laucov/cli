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
     * Provides data for testing the printer resource validation features.
     */
    public function resourceProvider(): array
    {
        $data_file = fopen('data://text/plain,foo', 'r');

        return [
            'no custom resource' => [true, null],
            'temporary file' => [true, tmpfile()],
            'unwritable but valid' => [true, $data_file],
            'string' => [false, 'foobar'],
            'array' => [false, [1, 2, ['foo', 'bar']]],
        ];
    }
    
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
     * @covers ::__construct
     * @dataProvider resourceProvider
     */
    public function testValidateResources(bool $is_valid, mixed $subject): void
    {
        if ($is_valid) {
            $this->expectNotToPerformAssertions();
        } else {
            $this->expectException(\InvalidArgumentException::class);
        }
        new Input($subject);
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
