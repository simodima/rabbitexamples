<?php

namespace Pugger\Command;

use Pugger\Consumer\LogConsumer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Logger extends Command
{
    private $logConsumerFactory;

    public function __construct(callable $logConsumerFactory)
    {
        parent::__construct(null);
        $this->logConsumerFactory = $logConsumerFactory;
    }

    public function configure()
    {
        $this
            ->setName('log')
            ->addArgument('channel', InputArgument::OPTIONAL, 'log channel', 'info' )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $channel  = $input->getArgument('channel');
        $factory = $this->logConsumerFactory;

        /** @var LogConsumer $consumer */
        $consumer = $factory($channel);

        foreach ($consumer->consume() as $log) {
            $output->writeln($log->body);
            $consumer->ack($log);
        }
    }
}
