<?php

namespace FeedsReaderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FeedsReaderAddCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('feeds-reader:add')
            ->setDescription('Add feed by console')
            ->addArgument('urls', InputArgument::REQUIRED, 'Feed Urls - Use commas for multiple feeds category.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $io = new SymfonyStyle($input, $output);
        $output->writeln(
            [
                'BEGIN READ FEEDS AND INSERT INTO DATABASE',
                '============',
                '',
            ]
        );
        $logger->info('BEGIN READ FEEDS AND INSERT INTO DATABASE\n');
        $io->progressStart(0);
        $urls = $input->getArgument('urls');

        $categoryHandler = $this->getContainer()->get('feeds_reader.category.handler');
        $result = $categoryHandler->save($urls);
        if($result['status']) {
            $logger->info('READ FEEDS AND INSERT INTO DATABASE - SCHEMA SUCCESS');
            $io->success('SCHEMA SUCCESS');
        } else {
            $io->error($result['message']);
            $logger->error('READ FEEDS AND INSERT INTO DATABASE - ' . $result['message']);
        }

        $io->progressFinish();
        $logger->info('END READ FEEDS AND INSERT INTO DATABASE\n');
        $output->writeln(
            [
                '',
                '============',
                'END READ FEEDS AND INSERT INTO DATABASE',
            ]
        );
    }

}
