<?php

declare(strict_types=1);

namespace Tests;

use Laucov\Cli\AbstractCommand;
use Laucov\Cli\AbstractRequest;
use Laucov\Cli\IncomingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Cli\AbstractCommand
 */
final class AbstractCommandTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::run
     * @uses Laucov\Cli\Printer::__construct
     * @uses Laucov\Cli\Printer::colorize
     * @uses Laucov\Cli\Printer::output
     * @uses Laucov\Cli\Printer::print
     * @uses Laucov\Cli\Printer::printLine
     */
    public function testCanRun(): void
    {
        // Create request.
        $request = $this->createMock(AbstractRequest::class);
        $request
            ->method('getArguments')
            ->willReturn(['John']);

        // Create command.
        $command = new class ($request) extends AbstractCommand {
            public function run(): void
            {
                $name = $this->request->getArguments()[0] ?? '';
                $this->printer->printLine("Hello, {$name}!");
            }
        };

        // Run.
        $this->expectOutputString("\e[0mHello, John!\e[0m\n");
        $command->run();
    }
}
