<?php

namespace FeedsReaderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FeedsReaderInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('feeds-reader:install')
            ->setDescription('Initial database for Feeds Reader Application');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $output->writeln(
            [
                'BEGIN INSTALL DATABASE FOR FEEDS READER APPLICATION',
                '============',
                '',
            ]
        );

        $io->progressStart();
        $output->writeln(
            [
                'DROP EXISTED DATABASES FIRST',
                '',
            ]
        );
        $command = $this->getApplication()->find('doctrine:database:drop');

        $dropDBInput = new ArrayInput(
            [
                'command' => 'doctrine:database:drop',
                '--force' => true,
                '--if-exists' => true
            ]
        );
        $command->run($dropDBInput, $output);

        $output->writeln(
            [
                '',
                'CREATE NEW DATABASE',
                '',
            ]
        );

        $command = $this->getApplication()->find('doctrine:database:create');

        $createDBInput = new ArrayInput(
            [
                'command' => 'doctrine:database:create',
            ]
        );
        if (!$command->run($createDBInput, $output)) {
            $io->success('CREATE NEW DATABASE SUCCESS');
        } else {
            $io->error('CREATE NEW DATABASE FAIL');
        }

        $command = $this->getApplication()->find('doctrine:schema:update');

        $createTableInput = new ArrayInput(
            [
                'command' => 'doctrine:schema:update',
                '--force' => true
            ]
        );

        if (!$command->run($createTableInput, $output)) {
            $io->success('SCHEMA SUCCESS');
        } else {
            $io->error('SCHEMA FAIL');
        }

        $io->progressFinish();
        $output->writeln(
            [
                '',
                '============',
                'END INSTALL DATABASE FOR FEEDS READER APPLICATION',
            ]
        );
    }

}
