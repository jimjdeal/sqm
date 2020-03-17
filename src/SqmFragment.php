<?php

class SqmFragment
{
    const TYPE_BOOL = 'bool';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';
    const TYPE_CLASS = 'class';

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var
     */
    private $value;

    /**
     * SqmFragment constructor.
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = str_replace("\r\n", '', $content);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if(!$this->name) $this->setNameAndValue();
        return $this->name;
    }

    public function getValue()
    {
        if(!$this->value) $this->setNameAndValue();
        return $this->value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        if(!$this->type) {
            if($this->isIntType()) $this->type = self::TYPE_INT;
            if($this->isFloatType()) $this->type = self::TYPE_FLOAT;
            if($this->isStringType()) $this->type = self::TYPE_STRING;
            if($this->isArrayType()) $this->type = self::TYPE_ARRAY;
            if($this->isClassType()) $this->type = self::TYPE_CLASS;
        }
        return $this->type;
    }

    private function setNameAndValue()
    {
        $type = $this->getType();
        switch ($type) {
            case self::TYPE_INT:
            case self::TYPE_FLOAT:
            case self::TYPE_STRING:
                list($name, $value) = explode('=', $this->content);
                if($type == self::TYPE_INT) $value = (int)$value;
                if($type == self::TYPE_FLOAT) $value = (float)$value;
                if($type == self::TYPE_STRING) $value = (string)trim($value, '"');
                break;
            case self::TYPE_ARRAY:
                list($name, $value) = $this->getFromArray($this->content);
                break;
            case self::TYPE_CLASS:
                list($name, $value) = $this->getFromClass($this->content);
                break;
            default:;
        }

        $this->name = $name ?? null;
        $this->value = $value ?? null;
    }

    /**
     * @return bool
     */
    private function isIntType(): bool
    {
        return preg_match('/^([a-zA-Z0-9]+)=(-?[0-9]+)$/', $this->content);
    }

    /**
     * @return bool
     */
    private function isFloatType(): bool
    {
        return preg_match('/^([a-zA-Z0-9]+)=(-?[0-9]+\.[0-9]+)$/', $this->content);
    }

    /**
     * @return bool
     */
    private function isArrayType(): bool
    {
        return preg_match('/^([a-zA-Z0-9]+\[\]=){(.*)}$/', $this->content);
    }

    /**
     * @return bool
     */
    private function isStringType(): bool
    {
        return preg_match('/^([a-zA-Z0-9]+)=(".*")$/', $this->content);
    }

    /**
     * @return bool
     */
    private function isClassType(): bool
    {
        return preg_match('/^((class) ([a-zA-Z0-9]+)){(.*)}$/', $this->content);
    }

    /**
     * @param string $content
     * @return array
     */
    private function getFromArray(string $content): array
    {
        $foo = explode('=', $this->content);
        $name = trim($foo[0], '[]');
        $value = [];
        foreach(explode(",", trim($foo[1], '{}')) as $item) {
            if(is_numeric($item)) $value[] = (float)$item == $item ? (float)$item : (int)$item;
            else $value[] = (string)trim($item, '"');
        }
        return [$name, $value];
    }

    /**
     * @param string $content
     * @return array
     */
    private function getFromClass(string $content): array
    {
        preg_match('/^(class) ([a-zA-Z0-9]+)/', $this->content, $matches);
        $name = substr($matches[0], 6);
        $value = trim(substr($this->content, strlen($name)+6), '{}');
        $value = SqmParser::getFromString($value)->parse();
        return [$name, $value];
    }
}