<?php

namespace Laucov\Cli;

/**
 * Provides CLI output utilities.
 */
class Printer
{
    /**
     * Blue background color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const BG_BLUE = 44;

    /**
     * Cyan background color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const BG_CYAN = 46;

    /**
     * Green background color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const BG_GREEN = 42;

    /**
     * Magenta background color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const BG_MAGENTA = 45;

    /**
     * Red background color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const BG_RED = 41;

    /**
     * Yellow background color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const BG_YELLOW = 43;

    /**
     * Blue text color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const TEXT_BLUE = 34;

    /**
     * Cyan text color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const TEXT_CYAN = 36;

    /**
     * Green text color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const TEXT_GREEN = 32;

    /**
     * Magenta text color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const TEXT_MAGENTA = 35;

    /**
     * Red text color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const TEXT_RED = 31;

    /**
     * Yellow text color.
     * 
     * @deprecated 2.0.0 Use `TextColor` and `BgColor` enums instead.
     */
    public const TEXT_YELLOW = 33;

    /**
     * Create the printer instance.
     * 
     * @param null|resource $resource File pointer to use as output.
     */
    public function __construct(
        /**
         * Custom output file pointer.
         * 
         * @var null|resource
         */
        protected mixed $resource = null,
    ) {
        if ($this->resource !== null && !is_resource($this->resource)) {
            $message = 'Invalid printer custom resource argument.';
            throw new \InvalidArgumentException($message);
        }
    }

    /**
     * Apply ANSI colors to the given text.
     */
    public function colorize(
        string $text,
        null|array|TextColor $text_color = null,
        null|BgColor $bg_color = null,
    ): string {
        // Organize colors.
        $colors = is_array($text_color) ? $text_color : [];
        if ($text_color !== null && !is_array($text_color)) {
            $colors[] = $text_color->value;
        }
        if ($bg_color !== null) {
            $colors[] = $bg_color->value;
        }

        // Start ANSI escaping.
        $result = "\e[0";
        foreach ($colors as $color) {
            // Fail if a non-integer color is passed.
            if (!is_int($color)) {
                $message = 'CLI ANSI colors must be integer numbers.';
                throw new \InvalidArgumentException($message);
            }
            // Add color.
            $result .= ';' . $color;
        }
        // Close ANSI escaping.
        $result .= 'm';

        // Add text and reset colors.
        $result .= $text . "\e[0m";

        return $result;
    }

    /**
     * Print the given text using the given colors.
     */
    public function print(
        string $text,
        null|array|TextColor $text_color = null,
        null|BgColor $bg_color = null,
    ): static {
        $message = $this->colorize($text, $text_color, $bg_color);
        $this->output($message);
        return $this;
    }

    /**
     * Print an individual line with the given text using the given colors.
     */
    public function printLine(
        string $text,
        null|array|TextColor $text_color = null,
        null|BgColor $bg_color = null,
    ): static {
        $this->print($text, $text_color, $bg_color);
        $this->output(PHP_EOL);
        return $this;
    }

    /**
     * Output raw data.
     */
    protected function output(string $data): void
    {
        if ($this->resource === null) {
            echo $data;
        } else {
            fwrite($this->resource, $data);
        }
    }
}
