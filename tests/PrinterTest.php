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
     * Provides data for testing the printer color features.
     */
    public function colorProvider(): array
    {
        return [
            'no colors' => [
                "\e[0mHello, World!\e[0m",
                'Hello, World!',
                null,
                null,
            ],
            'only text color' => [
                "\e[0;31mHello, World!\e[0m",
                'Hello, World!',
                TextColor::RED,
                null,
            ],
            'text and background color' => [
                "\e[0;32;41mHello, World!\e[0m",
                'Hello, World!',
                TextColor::GREEN,
                BgColor::RED,
            ],
            'only background color' => [
                "\e[0;46mHello, World!\e[0m",
                'Hello, World!',
                null,
                BgColor::CYAN,
            ],
        ];
    }

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
     * @covers ::colorize
     * @uses Laucov\Cli\Printer::__construct
     * @dataProvider colorProvider
     */
    public function testCanColorizeText(
        string $expected,
        string $text,
        null|TextColor $text_color,
        null|BgColor $bg_color,
    ): void {
        $actual = $this->printer->colorize($text, $text_color, $bg_color);
        $this->assertSame($expected, $actual);
    }

    /**
     * @coversNothing
     */
    public function testCanColorizeTextDeprecated(): void
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
    }

    /**
     * @covers ::output
     * @covers ::print
     * @covers ::printLine
     * @uses Laucov\Cli\Printer::__construct
     * @uses Laucov\Cli\Printer::colorize
     */
    public function testCanPrintText(): void
    {
        // Set output expectations.
        $this->expectOutputString(<<<TXT
            \e[0mWelcome to our awesome app!\e[0m
            \e[0;34mBlue \e[0m\e[0;41mRed \e[0m\e[0;31;43mYellow\e[0m
            \e[0mBye.\e[0m

            TXT);

        // Print.
        $this->printer->printLine('Welcome to our awesome app!');
        $this->printer->print('Blue ', TextColor::BLUE);
        $this->printer->print('Red ', null, BgColor::RED);
        $this->printer->printLine('Yellow', TextColor::RED, BgColor::YELLOW);
        $this->printer->printLine('Bye.');
    }

    /**
     * @coversNothing
     */
    public function testCanPrintTextDeprecated(): void
    {
        // Make example text.
        $colors = [Printer::TEXT_CYAN, Printer::BG_MAGENTA];
        $text = 'Hello, World!';

        // Set output expectations.
        $this->expectOutputString(<<<TXT
            \e[0;36;45mHello, World!\e[0m\e[0;36;45mHello, World!\e[0m

            TXT);

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
     * @covers ::__construct
     * @covers ::output
     * @uses Laucov\Cli\Printer::colorize
     * @uses Laucov\Cli\Printer::print
     * @uses Laucov\Cli\Printer::printLine
     */
    public function testCanUseCustomOutput(): void
    {
        $file = tmpfile();
        $printer = new Printer($file);
        $this->expectOutputString('');
        $printer->print('I like fries.');
        fseek($file, 0);
        $this->assertSame("\e[0mI like fries.\e[0m", fread($file, 1024));
    }

    /**
     * @covers ::colorize
     * @uses Laucov\Cli\Printer::__construct
     */
    public function testMustPassValidColorsDeprecated(): void
    {
        // Fail to use non-integer colors.
        $colors = [Printer::BG_RED, 'invalid color'];
        $this->expectException(\InvalidArgumentException::class);
        $this->printer->colorize('Hello, World!', $colors);
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
        new Printer($subject);
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->printer = new Printer();
    }
}
