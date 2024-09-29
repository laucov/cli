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
     * @covers ::askUntil
     * @covers ::readLine
     */
    public function testCanReadInput(): void
    {
        $fp = $this->createInputResource(<<<TXT
            do-something --foo --bar
            John
            b
            n
            si
            sí
            TXT);
        $this->expectOutputString(
            'Insert your name: '
                . 'Do it? [y/n] '
                . 'Invalid option. Insert "y" or "n".' . "\n"
                . 'Do it? [y/n] '
                . '¿proceder? '
                . '¿proceder? ',
        );
        $input = new Input($fp);
        $this->assertSame('do-something --foo --bar', $input->readLine());
        $this->assertSame('John', $input->ask('Insert your name:'));
        $value = $input->askUntil('Do it? [y/n]', function (string $value) {
            if (in_array($value, ['y', 'n'], true)) {
                return true;
            } else {
                echo 'Invalid option. Insert "y" or "n".' . PHP_EOL;
                return false;
            }
        });
        $this->assertSame('n', $value);
        $value = $input->askUntil('¿proceder?', function (string $value) {
            return in_array($value, ['sí', 'no'], true);
        });
        $this->assertSame('sí', $value);
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
