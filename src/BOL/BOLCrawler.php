<?php

namespace BOL;

use Symfony\Component\DomCrawler\Crawler;
use BOL\BOLLine;
use BOL\BOLParagraph;

/**
 * Class BOLCrawler
 * @package BOL
 */
class BOLCrawler
{
    /**
     * @var
     */
    private $url;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @param  $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param Crawler $crawler
     * @return BOLParagraph|bool
     */
    public function readParagraph(Crawler $crawler)
    {
        $paragraph = false;

        // @todo Only handle paragraph 1.1, 2.1, 2.1' and 2.1"
        $paragraphName = trim($crawler->filter('fieldset > legend')->text());

        // DEEL 1 -> 1.1
        if (strpos($paragraphName, 'Verantwoordelijke voor de verwerking'))
        {
            // Filter on fieldset and get the children of each fieldset
            $subParagraphs = $crawler->filter('fieldset')->children();

            // Get the legend element and next a set of table elements
            $lines = $subParagraphs->filter('legend')->siblings();

            // Handle paragraph1
            $paragraph = $this->_getPartOne($lines, $paragraphName);

            if (isset($paragraph))
            {
                return $paragraph;
            }
            else
            {
                return false;
            }
        }
        // DEEL 2 -> 2.1, 2.1' and 2.1"
        else
        {
            // This contains all paragraphs in DEEL 2
            // @todo Get the correct paragraph and handle it

            // Get the fieldsets -> not sure if this is needed!
            $paragraphs = $crawler->filter('fieldset'); // This probably also takes paragraph 1 again

            // PARAGRAPH 2.1
            if (strpos($paragraphName, 'Benaming van de verwerking'))
            {
                $paragraph = $this->_getPartTwo($paragraphs, $paragraphName);
            }
            // PARAGRAPH 2.1.1
            else if (strpos($paragraphName, 'Toegankelijkheid van de gefilmde plaats'))
            {
                $paragraph = $this->_getPartTwo($paragraphs, $paragraphName);
            }
            // PARAGRAPH 2.1.1.1
            else if (strpos($paragraphName, 'Localisatie van de bewakingscamera'))
            {
                $paragraph = $this->_getPartTwo($paragraphs, $paragraphName);
            }
            else
            {
                /**
                 *
                // BEGIN HACK for Categorieen gegevens die verwerkt worden
                if (strpos($paragraph, 'Categorieën van gegevens die verwerkt worden') != false)
                {
                    // Reduce table element which is NOT empty
                    $legendSiblings = $legendSiblings->reduce(function($node, $i) {
                        if (trim($node->text()) === "")
                        {
                            return false;
                        }
                    });
                }
                // END HACK

                // BEGIN HACK for 3. Categorieën van gegevens die verwerkt worden
                if (strpos($paragraph, 'Categorieën van gegevens die verwerkt worden') != false)
                {
                    $tableCrawler = $tableCrawler->filter('tr')->reduce(function($node, $i) {
                        if (trim($node->text()) === "")
                        {
                            return false;
                        }
                    });
                }
                // END HACK
                **/
            }

            if (isset($paragraph))
            {
                return $paragraph;
            }
            else
            {
                return false;
            }
        }
    }

    /**
     * @param Crawler $fieldsets
     * @param $paragraphName
     * @return BOLParagraph
     */
    private function _getPartTwo(Crawler $fieldsets, $paragraphName)
    {
        // Create object
        $paragraphObj = new BOLParagraph($paragraphName);

        $fieldsets->each(function($node, $i) use ($paragraphObj, $paragraphName){

            // For some reason the website is displayed differently in Firefox and Chrome.
            // There seems to be some server logic involved. Therefore we use this conversion table.
            $fieldsetChilds = $node->children();

            $legendSiblings = $fieldsetChilds->filter('legend')->siblings();

            // Get the tables
            $tableCrawler = $legendSiblings->filter('table');

            $tableCrawler->each(function($node, $i) use ($paragraphName, $paragraphObj) {
                $line = new BOLLine();

                // Get the number of rows
                $trCrawler = $node->filter('tr');

                // BEGIN HACK for 11. Algemene beschrijving van de veiligheidsmaatregelen en in het bijzonder beveiliging tegen toegang door onbevoegden
                if (iterator_count($trCrawler) == 1 && strpos($paragraphName, 'Algemene beschrijving van de veiligheidsmaatregelen en in het bijzonder beveiliging tegen toegang door onbevoegden') == false)
                // END HACK
                {
                    $tdText = $trCrawler->filter('td')->text();
                    $line->setLabel($paragraphName);
                    $line->setValue(trim($tdText));

                    if ($line->hasLine($line))
                    {
                        $paragraphObj->setLine($line);
                    }
                }
                else
                {
                    // HACK for 1" Localisatie van de bewakingscamera's
                    if (strpos($paragraphName, "Localisatie van de bewakingscamera's") != false)
                    {
                        $locations = array();

                        // Count number of <td> per row
                        $trCrawler->each(function(Crawler $node, $i) use (&$locations) {
                            $location = trim($node->text());

                            if ($location !== "" && strpos($location, 'Sites van bewakingscamera') == false && $node->attr('colspan') != "2")
                            {
                                array_push($locations, $location);
                            }
                        });

                        $line->setLabel($paragraphName);
                        $line->setValue($locations);

                        if ($line->hasLine($line))
                        {
                            $paragraphObj->setLine($line);
                        }

                        //print_r($paragraphObj);
                    }
                    // END HACK
                }
            });
        });
        return $paragraphObj;
    }

    /**
     * @param Crawler $crawler
     * @param $name
     * @return BOLParagraph|bool
     */
    private function _getPartOne(Crawler $crawler, $name)
    {
        $paragraph = new BOLParagraph($name);

        if ($crawler instanceof Crawler)
        {
            $crawler->each(function($_sibling) use ($paragraph) {
                $line = new BOLLine();
                $table_sibling_child_crawler = $_sibling->filter('table > tr');

                $_td_crawler = $table_sibling_child_crawler->reduce(function($_child) {
                    $_tr_object = $_child->filter('tr')->children();
                    if (iterator_count($_tr_object) == 1) {
                        return false;
                    }
                });

                $_tr_crawler = $table_sibling_child_crawler->reduce(function($_child) {
                    $_tr_object = $_child->filter('tr')->children();
                    if (iterator_count($_tr_object) > 1) {
                        return false;
                    }
                });

                $_td_crawler->each(function($_td) use ($paragraph) {
                    $line = new BOLLine();
                    $_td_crawler_children = $_td->filter('tr')->children();
                    foreach($_td_crawler_children as $id => $value) {
                        if (($id%2) == 0) {
                            if (trim($value->nodeValue) != "") {
                                //print_r('Label :' . trim($value->nodeValue) . "\n");
                                $line->setLabel(trim($value->nodeValue));
                            }
                        }
                        else {
                            if (trim($value->nodeValue) != "") {
                                //print_r('Value : ' . trim($value->nodeValue) . "\n");
                                $line->setValue(trim($value->nodeValue));
                            }
                        }
                        if ($line->hasLine($line)) {
                            $paragraph->setLine($line);
                            $line = new BOLLine();
                        }
                    }

                });

                $conversion = array(
                    '0' => 'Natuurlijk persoon',
                    '1' => 'Rechtspersoon',
                    '01' => 'Privé-persoon',
                    '04' => 'Apotheker',
                    '11' => 'Besloten vennootschap met beperkte aansprakelijkheid (BVBA)',
                    '14' => 'Naamloze vennootschap (NV)',
                );
                $hackJuridisch = false;
                foreach($_tr_crawler as $id => $value) {
                    $value = trim($value->nodeValue);
                    // HACK for Juridisch statuut van de verantwoordelijke voor de verwerking
                    if (strpos($value, 'Juridisch statuut') !== false )
                    {
                        $hackJuridisch = true;
                        break;
                    }
                    else {
                        if (($id%2) == 0) {
                            if ($value != "") {
                                $line->setLabel($value);
                            }
                        }
                        else {
                            if ($value != "") {
                                $line->setValue($value);
                            }
                        }
                        if ($line->hasLine($line)) {
                            $paragraph->setLine($line);
                            $line = new BOLLine();
                        }
                    }
                }

                // HACK for Juridisch statuut van de verantwoordelijke voor de verwerking
                if ($hackJuridisch) {
                    $line = new BOLLine();
                    $list = '';
                    foreach($_tr_crawler as $id => $value) {
                        $value = trim($value->nodeValue);
                        // Get the label in the id
                        if ($id == 0) {
                            $line->setLabel($value);
                        }
                        else {
                            if (isset($conversion[$value]))
                            {
                                $value = $conversion[$value];
                            }
                            $list .= trim($value);
                            $list .= ', ';
                        }
                    }
                    // Remove the final character
                    $line->setValue(substr(trim($list), 0, -1));
                    $paragraph->setLine($line);
                }
            });

            return $paragraph;
        }
        else
        {
            return FALSE;
        }

    }
}