<?php

namespace BOL;

/**
 * Class BOLLine
 * @package BOL
 */
class BOLLine {

    /**
     * @var
     */
    private $label;
    /**
     * @var
     */
    private $value;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @param $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param BOLLine $line
     * @return bool
     */
    public function hasLine(BOLLine $line) {
        if (isset($line->label) && isset($line->value)) {
            return  TRUE;
        }
        else {
            return FALSE;
        }
    }
}