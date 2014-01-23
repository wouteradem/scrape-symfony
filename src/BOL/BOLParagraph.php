<?php

namespace BOL;

/**
 * Class BOLParagraph
 * @package BOL
 */
class BOLParagraph {

    /**
     * @var int
     */
    private $id;
    /**
     * @var
     */
    private $name;
    /**
     * @var array
     */
    private $paragraph = array();

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->id = mt_rand();
        $this->name = $name;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $paragraph
     */
    public function setParagraph($paragraph)
    {
        $this->paragraph = $paragraph;
    }

    /**
     * @return array
     */
    public function getParagraph()
    {
        return $this->paragraph;
    }

    /**
     * @param $line
     */
    public function setLine($line)
    {
        array_push($this->paragraph, $line);
    }
}