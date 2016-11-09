<?php

namespace BOL;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use BOL\BOLCrawler;
use BOL\BOLPage;

/**
 * Class BOLBook
 * @package BOL
 */
class BOLBook
{
    /**
     * @var
     */
    private $title;
    /**
     * @var
     */
    private $begin;
    /**
     * @var
     */
    private $end;
    /**
     * @var array
     */
    private $book = array();

    /**
     * @param $title
     * @param $begin
     * @param $end
     */
    public function __construct($title, $begin, $end)
    {
        $this->title = $title;
        $this->begin = $begin;
        $this->end = $end;
    }

    /**
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $begin
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
    }

    /**
     * @return mixed
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * @param $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param $book
     */
    public function setBook($book)
    {
        $this->book = $book;
    }

    /**
     * @return array
     */
    public function getBook()
    {
        return $this->book;
    }

    /**
     *
     */
    public function read(BOLCrawler $bolCrawler, Client $client)
    {
        $container = new \Pimple();

        // Set Serializer
        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $container['serializer'] = $container->share(function() use ($normalizers, $encoders) {
            return new Serializer($normalizers, $encoders);
        });

        for ($i=$this->begin; $i<$this->end; $i++)
        {
            try
            {
                // Get web page
                $crawler = $client->request('GET', $this->title . $i);

                // First check if we do not get a 500 ERROR
                if (strpos(trim($crawler->text()), '500')) {
                    print_r('Node ' . $i . ' returned a HTTP status code 500.'.PHP_EOL);
                }
                else
                {
                    if (isset($crawler))
                    {
                        // @todo Preprocess crawler to remove obsolete elements

                        // Create Page
                        $page = new BOLPage($crawler, $i);

                        $page->readPage($bolCrawler, $crawler);

                        // Set Book
                        array_push($this->book, $page);

                    }
                }
            }
            catch (\Exception $e) {
                print_r('Fatal error for node ' . $i . ' :' . $e);
            }
        }

        // Serialize to Page to JSON
        $jsonContent = $container['serializer']->serialize($this->book, 'json');

        // Log
        $this->write($jsonContent);
    }

    /**
     * @param $json
     */
    private function write($json) {
        $fs = new FileSystem();
        try
        {
            if (!$fs->exists(__DIR__.'data/json'))
            {
                $fs->mkdir(__DIR__.'/data/json');
            }
            $fileName = mt_rand() . '.json';
            $fs->dumpFile(__DIR__.'/data/json/'.$fileName, $json);
        }
        catch(IOException $e)
        {
            echo "Error: could not create the directory at ".$e->getPath();
        }
    }
}
