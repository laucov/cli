<?php

declare(strict_types=1);

namespace Tests;

use Laucov\Cli\IncomingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Cli\IncomingRequest
 */
final class IncomingRequestTest extends TestCase
{
    /**
     * Provides request testing parameters.
     */
    public function paramProvider(): array
    {
        return [
            [
                [],
                null,
                null,
                [],
            ],
            [
                ['script.php'],
                'script.php',
                null,
                [],
            ],
            [
                ['script.php', 'just-do'],
                'script.php',
                'just-do',
                [],
            ],
            [
                ['script.php', 'just-do', 'it'],
                'script.php',
                'just-do',
                ['it'],
            ],
            [
                ['script.php', 'just-do', 'it', 'now'],
                'script.php',
                'just-do',
                ['it', 'now'],
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::getArguments
     * @covers ::getCommand
     * @covers ::getFilename
     * @dataProvider paramProvider
     */
    public function testCanGetInformation(
        array $raw_arguments,
        null|string $filename,
        null|string $command,
        array $arguments,
    ): void {
        // Create request.
        $request = new IncomingRequest($raw_arguments);

        // Check filename and command.
        $this->assertSame($filename, $request->getFilename());
        $this->assertSame($command, $request->getCommand());

        // Check arguments.
        $actual_args = $request->getArguments();
        foreach ($arguments as $i => $arg) {
            $this->assertSame($arg, $actual_args[$i]);
        }
        $this->assertSameSize($arguments, $actual_args);
    }

    /**
     * @covers ::__construct
     */
    public function testArgumentsMustBeStrings(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new IncomingRequest(['script.php', 123, [], 'argument']);
    }
}
