<?php

namespace App\Command;

use App\Service\WeatherUpdateService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:weather:update',
    description: 'Update weather for a given frequency (hourly|daily)',
)]
class WeatherUpdateCommand extends Command
{
    private WeatherUpdateService $weatherUpdateService;

    public function __construct(WeatherUpdateService $weatherUpdateService)
    {
        parent::__construct();
        $this->weatherUpdateService = $weatherUpdateService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('frequency', InputArgument::REQUIRED, 'Frequency type: hourly or daily');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $frequency = strtolower($input->getArgument('frequency'));

        if (!in_array($frequency, ['hourly', 'daily'])) {
            $output->writeln('<error>Invalid frequency. Use "hourly" or "daily".</error>');
            return Command::FAILURE;
        }

        $this->weatherUpdateService->updateByFrequency($frequency);
        $output->writeln("Weather update for '{$frequency}' subscriptions completed.");

        return Command::SUCCESS;
    }
}
