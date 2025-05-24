<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:projections:initialize')]
class InitializeAllProjections extends Command
{
    /**
     * @param list<string> $projections
     */
    public function __construct(
        private readonly array $projections
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Initializing projections:');
        foreach ($this->projections as $projection) {
            $greetInput = new ArrayInput([
                'command' => 'ecotone:es:initialize-projection',
                'name' => $projection,
            ]);

            $greetInput->setInteractive(false);
            $output->writeln('- '.$projection);
            try {
                $this->getApplication()?->doRun($greetInput, $output);
            } catch (\Throwable) {
                continue;
            }
        }

        return Command::SUCCESS;
    }
}
