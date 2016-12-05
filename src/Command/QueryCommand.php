<?php

namespace App\Command;

use App\Interfaces\ContainerAwareInterface;
use App\Traits\ContainerAwareTrait;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class QueryCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('Query Trello to the list id to use for calendar event cards')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container   = $this->getContainer();
        $config      = $container['app.config'];
        $trello      = $container['api.trello'];
        $questionner = $this->getHelper('question');

        $username    = $config->get('trello.username');
        $output->writeln('Querying the trello API for user ' . $username);
        $boards      = $trello->members()->boards()->all($username);
        if (0 == count($boards)) {
            $output->writeln('No boards found - you\'ll need at least one!');
            return;
        }
        $output->writeln(sprintf('Found %d boards', count($boards)));
        $output->writeln('Choose a board to look for lists');
        $options = [];
        foreach ($boards as $board) {
            $options[$board['id']] = $board['name'];
        }
        $question = new ChoiceQuestion(
            'Choose a board to look for lists',
            array_values($options)
        );
        $board  = $questionner->ask($input, $output, $question);
        $choice = array_search($board, $options);
        $output->writeln(sprintf('Querying for lists for board %s (%s)', $board, $choice));
        $lists  = $trello->boards()->lists()->all($choice);
        if (0 == count($lists)) {
            $output->writeln(sprintf('Board %s has no lists', $board));
            return;
        }
        $output->writeln('Choose a list to put events into');
        $options = [];
        foreach ($lists as $list) {
            $options[$list['id']] = $list['name'];
        }
        $question = new ChoiceQuestion(
            'Choose a list',
            array_values($options)
        );
        $list   = $questionner->ask($input, $output, $question);
        $choice = array_search($list, $options);
        $output->writeln(sprintf('You chose list %s. The list id you need is', $list));
        $output->writeln('');
        $output->writeln('   ' . $choice);
        $output->writeln('');
    }
}
