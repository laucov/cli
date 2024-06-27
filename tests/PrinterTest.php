<?php

declare(strict_types=1);

namespace Tests;

use Laucov\Cli\BgColor;
use Laucov\Cli\Printer;
use Laucov\Cli\TextColor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Cli\Printer
 */
final class PrinterTest extends TestCase
{
    /**
     * Printer instance.
     */
    protected Printer $printer;

    /**
     * @covers ::colorize
     */
    public function testCanColorizeText(): void
    {
        // Check color avaibility.
        $colors = [
            Printer::BG_BLUE,
            Printer::BG_CYAN,
            Printer::BG_GREEN,
            Printer::BG_MAGENTA,
            Printer::BG_RED,
            Printer::BG_YELLOW,
            Printer::TEXT_BLUE,
            Printer::TEXT_CYAN,
            Printer::TEXT_GREEN,
            Printer::TEXT_MAGENTA,
            Printer::TEXT_RED,
            Printer::TEXT_YELLOW,
        ];
        
        // Make example text.
        $text = 'Hello, World!';

        // Test without colors.
        $result = $this->printer->colorize($text, []);
        $this->assertSame("\e[0mHello, World!\e[0m", $result);

        // Test single color.
        $colors = [Printer::TEXT_RED];
        $result = $this->printer->colorize($text, $colors);
        $this->assertSame("\e[0;31mHello, World!\e[0m", $result);

        // Test multiple colors.
        $colors = [Printer::BG_RED, Printer::TEXT_GREEN];
        $result = $this->printer->colorize($text, $colors);
        $this->assertSame("\e[0;41;32mHello, World!\e[0m", $result);

        // Fail to use non-integer colors.
        $colors = [Printer::BG_RED, 'invalid color'];
        $this->expectException(\InvalidArgumentException::class);
        $this->printer->colorize($text, $colors);
    }

    /**
     * @covers ::print
     * @covers ::printLine
     * @uses Laucov\Cli\Printer::colorize
     */
    public function testCanPrintText(): void
    {
        // Make example text.
        $colors = [Printer::TEXT_CYAN, Printer::BG_MAGENTA];
        $text = 'Hello, World!';

        // Set output expectations.
        $this->expectOutputString(<<<SQL
            \e[0;36;45mHello, World!\e[0m\e[0;36;45mHello, World!\e[0m

            SQL);

        // Print without new line.
        $this->assertSame(
            $this->printer,
            $this->printer->print($text, $colors),
        );

        // Print with new line.
        $this->assertSame(
            $this->printer,
            $this->printer->printLine($text, $colors),
        );
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->printer = new Printer();
    }
}
