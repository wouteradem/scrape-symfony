<?php

namespace BOL;

use Symfony\Component\DomCrawler\Crawler;
use \DateTime;
use BOL\BOLCrawler;

/**
 * Class BOLPage
 * @package BOL
 */
class BOLPage
{
    /**
     * @var
     */
    private $id;
    /**
     * @var bool
     */
    private $created;
    /**
     * @var array|bool
     */
    private $published = array();
    /**
     * @var array
     */
    private $page = array();

    /**
     * @param Crawler $crawler
     * @param $id
     */
    public function __construct(Crawler $crawler, $id)
    {
        $this->id = $id;
        $timestamp = $this->_getTimestamp($crawler);
        if (isset($timestamp))
        {
            $this->created = isset($timestamp['created']) ? $timestamp['created'] : false;
            $this->published = isset($timestamp['published']) ? empty($timestamp['published']) ?  false : $timestamp['published'] : false;
        }
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return array
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return bool
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return array|bool
     */
    public function getPublished()
    {
        return $this->published;
    }


    /**
     * @param BOLCrawler $reader
     * @param Crawler $crawler
     * @return array
     */
    public function readPage(BOLCrawler $reader, Crawler $crawler) {
        $page = array();
        $crawler->filter('fieldset')->each(function(Crawler $node, $i) use ($reader, &$page) {

           // Get paragraph
           $paragraph = $reader->readParagraph($node);
           if (isset($paragraph))
           {
               // Set paragraph
               array_push($page, $paragraph);
           }
        });
        $this->setPage($page);
    }

    /**
     * @param Crawler $crawler
     * @return array
     */
    private function _getTimestamp(Crawler $crawler)
    {
        //BEGIN HACK Get the second table node
        $node = $crawler->filter('table')->eq(1)->first();
        //END HACK

        $timestamps = array();
        $published = array();
        $node->filter('tr > td')->each(function($node, $i) use (&$timestamps, &$published) {
            if (isset($node))
            {
                if (strpos($node->text(), 'Aangifte'))
                {
                    $date = explode(':', trim($node->text()));
                    $date = trim($date[1]);
                    $date = DateTime::createFromFormat('d/m/Y', $date)->format('d-m-Y');
                    $timestamps['created'] = $date;
                }
                else if (strpos($node->text(), 'Wijziging'))
                {
                    $date = explode(':', trim($node->text()));
                    $date = trim($date[1]);
                    $date = DateTime::createFromFormat('d/m/Y', $date)->format('d-m-Y');
                    array_push($published, $date);
                }
            }
            else {
                return false;
            }
        });
        $timestamps['published'] = $published;

        return $timestamps;
    }
}