<?php

class SqmParser
{
    private $handle;

    /**
     * SqmParser constructor.
     * @param $fileHandler
     */
    private function __construct($fileHandler)
    {
        $this->handle = $fileHandler;
    }

    public function __destruct()
    {
        fclose($this->handle);
    }

    /**
     * @param string $filepath
     * @return SqmParser
     */
    public static function getFromFile(string $filepath): SqmParser
    {
        $handle = fopen($filepath, 'r');
        return new self($handle);
    }

    /**
     * @param string $content
     * @return SqmParser
     */
    public static function getFromString(string $content): SqmParser
    {
        $handle = tmpfile();
        fwrite($handle, $content);
        fseek($handle, 0);
        return new self($handle);
    }

    /**
     * @return Sqm
     */
    public function parse(): Sqm
    {
        $sqmFile = new Sqm();

        $segments = $this->segmentation();
        foreach($segments as $segment) {
            $this->addSegment($sqmFile, $segment);
        }

        return $sqmFile;
    }

    /**
     * @return array
     */
    private function segmentation(): array
    {
        $segments = [];
        $buffer = '';
        $brackets = 0;
        $x = 0;

        while (false !== ($char = fgetc($this->handle))) {
            if($char == "\t") continue;
            if($char == '{') $brackets++;
            if($char == '}') $brackets--;
            if($char == '"') $x++;

            if($char == ';' && $brackets == 0 && $x % 2 == 0) {
                $segments[] = new SqmFragment($buffer);
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }

        return $segments;
    }

    /**
     * @param Sqm $sqmFile
     * @param SqmFragment $sqmFragment
     */
    private function addSegment(Sqm $sqmFile, SqmFragment $sqmFragment)
    {
        $sqmFile->{$sqmFragment->getName()} = $sqmFragment->getValue();
    }
}