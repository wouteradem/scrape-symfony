<?php

namespace BOL\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use BOL\BOLCrawler;
use BOL\BOLBook;
use Goutte\Client;

require_once __DIR__.'/../../../goutte.phar';

class BOLScrapeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('scrape:website')
            ->setDescription('Scrapes a website')
            ->addArgument(
                'begin',
                InputArgument::REQUIRED,
                'Please fill in an integer'
            )
            ->addArgument(
                'end',
                InputArgument::REQUIRED,
                'Please fill in an integer'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $begin = $input->getArgument('begin');
        $end = $input->getArgument('end');

        $error = false;
        if (!$begin || !$end) {
            $error = true;
        }
        if ($error) {
            $output->writeln('Please enter an argument in order to scrape.');
        }
        else {
            $container = new \Pimple();

            require __DIR__.'/../../../app/config.php';

            // BEGIN HACK to avoid DateTime PHP warnings
            date_default_timezone_set('Europe/Brussels');
            // END HACK to avoid DateTime PHP warnings

            // Create a Book
            $container['book'] = $container->share(function(\Pimple $container) use ($begin, $end) {
                return new BOLBook(
                    $container['book.title'],
                    $begin,
                    $end
                );
            });

           // Create Reader service
            $title = $container['book']->getTitle();
            $container['reader'] = $container->share(function() use ($title) {
                return new BOLCrawler($title);
            });

            // Create Client service
            $container['client'] = $container->share(function() {
                return new Client();
            });

            // Read the Book
            $container['book']->read($container['reader'], $container['client']);
        }

    }
}