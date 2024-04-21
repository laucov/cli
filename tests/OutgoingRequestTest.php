<?php

declare(strict_types=1);

namespace Tests;

use Laucov\Cli\OutgoingRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Cli\OutgoingRequest
 */
final class OutgoingRequestTest extends TestCase
{
    protected OutgoingRequest $request;

    protected function setUp(): void
    {
        $this->request = new OutgoingRequest();
    }

    /**
     * @covers ::setArguments
     */
    public function testArgumentsMustBeStrings(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->request->setArguments(['arg1', 0, true]);
    }

    /**
     * @covers ::getArguments
     * @covers ::setArguments
     */
    public function testCanSetArguments(): void
    {
        // Set and get arguments.
        $arguments = ['arg1', 'arg2', 'arg3'];
        $actual_arguments = $this->request
            ->setArguments($arguments)
            ->getArguments();

        // Test received arguments.
        foreach ($arguments as $i => $argument) {
            $this->assertSame($arguments[$i], $actual_arguments[$i]);
        }
        $this->assertSameSize($arguments, $actual_arguments);
    }

    /**
     * @covers ::getCommand
     * @covers ::setCommand
     */
    public function testCanSetCommand(): void
    {
        // Set and get the command.
        $command = $this->request
            ->setCommand('do-something')
            ->getCommand();
        $this->assertSame('do-something', $command);
    }
}
