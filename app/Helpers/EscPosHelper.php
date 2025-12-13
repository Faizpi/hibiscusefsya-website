<?php

namespace App\Helpers;

class EscPosHelper
{
    // ESC/POS Commands
    const ESC = "\x1B";
    const GS = "\x1D";
    const LF = "\x0A";
    const CR = "\x0D";
    
    // Text alignment
    const ALIGN_LEFT = 0;
    const ALIGN_CENTER = 1;
    const ALIGN_RIGHT = 2;
    
    private $buffer = '';
    
    public function __construct()
    {
        $this->initialize();
    }
    
    /**
     * Initialize printer
     */
    public function initialize()
    {
        $this->buffer .= self::ESC . "@";
        return $this;
    }
    
    /**
     * Set text alignment
     */
    public function align($align = self::ALIGN_LEFT)
    {
        $this->buffer .= self::ESC . "a" . chr($align);
        return $this;
    }
    
    /**
     * Set text bold
     */
    public function bold($bold = true)
    {
        $this->buffer .= self::ESC . "E" . ($bold ? chr(1) : chr(0));
        return $this;
    }
    
    /**
     * Set text size
     */
    public function textSize($width = 1, $height = 1)
    {
        $width = max(1, min(8, $width));
        $height = max(1, min(8, $height));
        $n = (($width - 1) << 4) | ($height - 1);
        $this->buffer .= self::GS . "!" . chr($n);
        return $this;
    }
    
    /**
     * Print text
     */
    public function text($text = '')
    {
        $this->buffer .= $text;
        return $this;
    }
    
    /**
     * Print text with newline
     */
    public function line($text = '')
    {
        $this->buffer .= $text . self::LF;
        return $this;
    }
    
    /**
     * Feed lines
     */
    public function feed($lines = 1)
    {
        for ($i = 0; $i < $lines; $i++) {
            $this->buffer .= self::LF;
        }
        return $this;
    }
    
    /**
     * Cut paper
     */
    public function cut($mode = 0)
    {
        $this->buffer .= self::GS . "V" . chr($mode);
        return $this;
    }
    
    /**
     * Get buffer content
     */
    public function getBuffer()
    {
        return $this->buffer;
    }
    
    /**
     * Clear buffer
     */
    public function clear()
    {
        $this->buffer = '';
        return $this;
    }
    
    /**
     * Helper: Print separator line
     */
    public function separator($char = '=', $length = 32)
    {
        $this->line(str_repeat($char, $length));
        return $this;
    }
    
    /**
     * Helper: Print two column text
     */
    public function twoColumn($left, $right, $width = 32)
    {
        $rightLen = mb_strlen($right);
        $leftLen = $width - $rightLen;
        $this->line(str_pad($left, $leftLen) . $right);
        return $this;
    }
    
    /**
     * Output as raw ESC/POS data (base64 encoded for web)
     */
    public function output()
    {
        return base64_encode($this->buffer);
    }
    
    /**
     * Output as raw ESC/POS data
     */
    public function outputRaw()
    {
        return $this->buffer;
    }
}
