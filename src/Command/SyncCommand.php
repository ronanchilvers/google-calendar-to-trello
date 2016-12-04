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
                'd',
                InputOption::VALUE_REQUIRED,
                'The date to sync calendar events for, eg: 2016-12-07'
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $config    = $container['app.config'];
        $google    = $container['api.calendar'];
        $trello    = $container['api.trello'];

        $date      = $input->getOption('date');
        if (!is_null($date)) {
            $today = new Carbon($date);
        } else {
            $today = Carbon::now();
        }
        $today->setTime('00', '00', '00');
        $tomorrow  = $today->copy()->setTime('23', '59', '59');
        $output->writeln('Syncing events for ' . $today->format('jS F, Y'));

        $calendar  = 'primary'; // This should come out of the config. Maybe an array?
        $options   = [
          'maxResults'   => 20,
          'orderBy'      => 'startTime',
          'singleEvents' => true,
          'timeMin'      => $today->format('c'),
          'timeMax'      => $tomorrow->format('c'),
        ];
        $events = $google->events->listEvents($calendar, $options);
        if (0 == count($events)) {
            $output->writeln('No events found');
            return;
        }
        $count = 0;
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
                'idList' => $config->get('trello.list'),
                'name'   => $event->getSummary(),
                'desc'   => $event->getDescription(),
                'due'    => $start->format('c'),
            ];
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
