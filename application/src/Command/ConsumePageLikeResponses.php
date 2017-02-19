<?php

namespace Pugger\Command;

use Pugger\Consumer\PageLikesResponseConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumePageLikeResponses extends Command
{
    private $pageLikeConsumer;

    public function __construct(PageLikesResponseConsumer $pageLikeConsumer)
    {
        parent::__construct(null);
        $this->pageLikeConsumer = $pageLikeConsumer;
    }

    public function configure()
    {
        $this->setName('app:consumer:page_like');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Waiting for page like responses...');
        foreach ($this->pageLikeConsumer->consume() as $message) {
            $output->writeln($message->body);
            $this->pageLikeConsumer->ack($message);
        }
    }
}
