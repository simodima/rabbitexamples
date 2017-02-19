<?php

namespace Pugger\Command;

use Pugger\Downloader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloaderCommand extends Command
{
    private $downloader;

    public function __construct(Downloader $downloader)
    {
        parent::__construct(null);
        $this->downloader = $downloader;
    }

    public function configure()
    {
        $this
            ->setName('app:downloader:start')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Downloader started...');
        $this->downloader->start();
    }
}
