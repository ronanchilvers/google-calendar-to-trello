<?php

namespace App\Command;

use App\Interfaces\ContainerAwareInterface;
use App\Traits\ContainerAwareTrait;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('sync')
            ->setDescription('Sync calendar events with Trello')
            ->addOption(
                'date',
                null,
                InputOption::VALUE_REQUIRED,
                'The date to sync calendar events for, eg: 2016-12-07'
            )
            ->addOption(
                'list',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify a different list id to put cards into'
            )
            ->addOption(
                'labels',
                null,
                InputOption::VALUE_REQUIRED,
                'A (comma separated) set of labels to apply to new cards'
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $config    = $container['app.config'];
        $trello    = $container['api.trello'];

        $date      = $input->getOption('date');
        if (!is_null($date)) {
            $today = new Carbon($date);
        } else {
            $today = Carbon::now();
        }
        $today->setTime('00', '00', '00');
        $tomorrow  = $today->copy()->setTime('23', '59', '59');
        $output->writeln('Sync Date : ' . $today->format('jS F, Y'));

        $listId = $input->getOption('list');
        if (is_null($listId)) {
            $listId = $config->get('trello.list');
        }
        $output->writeln('List id : ' . $listId);
        $labelString = $labels = $labelNames = $labelColors = false;
        if (!is_null($input->getOption('labels'))) {
            $labels = explode(',', $input->getOption('labels'));
            $labels = array_map(function ($value) {
                return trim($value);
            }, $labels);
            $output->writeln('Labels:');
            $output->writeln(' - ' . implode("\n - ", $labels));

            $output->writeln('Querying for label data...');
            $list      = $trello->lists()->show($listId);
            $labelData = $trello->boards()->labels()->all($list['idBoard']);
            if (0 < count($labelData)) {
                $labelNames = $labelColors = [];
                foreach ($labelData as $data) {
                    $label                       = empty($data['name']) ? $data['color'] : $data['name'];
                    $labelNames[$label]          = $data['id'];
                    $labelColors[$data['color']] = $data['id'];
                }
            }
            $labelString = [];
            foreach ($labels as $label) {
                if (isset($labelNames[$label])) {
                    $labelString[] = $labelNames[$label];
                    continue;
                }
                if (isset($labelColors[$label])) {
                    $labelString[] = $labelColors[$label];
                    continue;
                }
                $output->writeln(sprintf('Dropping missing label %s', $label));
            }
            $labelString = implode(',', $labelString);
        }

        $google    = $container['api.calendar'];
        $calendar  = 'primary'; // This should come out of the config. Maybe an array?
        $options   = [
          'maxResults'   => 20,
          'orderBy'      => 'startTime',
          'singleEvents' => true,
          'timeMin'      => $today->format('c'),
          'timeMax'      => $tomorrow->format('c'),
        ];
        $output->writeln('Querying google...');
        $events = $google->events->listEvents($calendar, $options);
        if (0 == count($events)) {
            $output->writeln('No events found');
            return;
        }
        $count  = 0;
        foreach ($events as $event) {
            $id = $event->getId();
            $start = $event->start->dateTime;
            if (empty($start)) {
                $start = $event->start->date;
            }
            $end = $event->end->dateTime;
            if (empty($end)) {
                $end = $event->end->date;
            }
            $start = new Carbon($start);
            $end   = new Carbon($end);
            $output->writeln(sprintf("%s : %s", $event->getSummary(), $start));

            $attendees = [];
            foreach ($event->getAttendees() as $attendee) {
                $attendees[] = sprintf('%s <%s>', $attendee->getDisplayName(), $attendee->getEmail());
            }
            $params = [
                'idList' => $listId,
                'name'   => $event->getSummary(),
                'desc'   => $event->getDescription(),
                'due'    => $start->format('c'),
            ];
            if (is_string($labelString)) {
                $params['idLabels'] = $labelString;
            }
            $card = $trello->cards()->create($params);

            if (0 < count($attendees)) {
                $checklist = $trello->checklists()->create([
                    'idCard' => $card['id'],
                    'name' => 'Attendees'
                ]);
                foreach ($attendees as $attendee) {
                    $trello->checklists()->items()->create(
                        $checklist['id'],
                        $attendee
                    );
                }
            }
            $count++;
        }
        $output->writeln('Synced ' . $count . ' events');
    }
}
