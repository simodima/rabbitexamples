<?php

namespace Pugger\Command;

use Pugger\Producer\PageLikeRequestProducer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PublishRandomMessages extends Command
{
    private $downloadRequest;

    public function __construct(PageLikeRequestProducer $downloadRequest)
    {
        parent::__construct(null);
        $this->downloadRequest = $downloadRequest;
    }

    public function configure()
    {
        $this
            ->setName('app:simulate')
            ->addOption('pages', 'p', InputOption::VALUE_OPTIONAL, 'The number of pages per user', 10)
            ->addOption('users', 'u', InputOption::VALUE_OPTIONAL, 'The number of users', 10)
        ;
    }

    private function xrange($size)
    {
        for($i = 0; $i <= $size; $i++) {
            yield $i;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $p = (int) $input->getOption('pages');
        $u = (int) $input->getOption('users');

        $users = $this->xrange($u);
        $output->writeln("Publishing $p pages per user with $u users (Total: ".($p * $u)." messages)");

        foreach ($users as $user) {
            foreach ($this->xrange($p) as $page) {
                $fakeToken = md5($user);
                $this->downloadRequest->requestPageLikes($user, $fakeToken, $page);
            }
        }
    }
}
