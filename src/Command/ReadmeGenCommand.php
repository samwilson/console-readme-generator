<?php

declare(strict_types=1);

namespace Samwilson\ConsoleReadmeGenerator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReadmeGenCommand extends Command
{
    public function configure(): void
    {
        parent::configure();
        $this->setName('generate-readme');
        $this->setDescription('Generate command documentation for a Readme file.');
        $this->setHidden(true);
        $this->addOption(
            'include',
            'i',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Explicitly include a command.',
            []
        );
        $this->addOption(
            'readme',
            'r',
            InputOption::VALUE_REQUIRED,
            'Path (including filename) of the README file to modify.',
            getcwd() . '/README.md'
        );
        $this->addOption(
            'usage',
            'u',
            InputOption::VALUE_REQUIRED,
            'Name of the section in the README file in which to insert the documentation.',
            'Usage'
        );
    }

    /**
     * @return Command[]
     */
    private function getCommands(InputInterface $input): array
    {
        $included = $input->getOption('include');
        $allCommands = $this->getApplication()->all();
        $out = [];
        $defaultExcluded = ['help', 'list', $this->getName()];
        foreach ($allCommands as $command) {
            $include = $included
                ? in_array($command->getName(), $included)
                : !in_array($command->getName(), $defaultExcluded);
            if ($include) {
                $out[] = $command;
            }
        }
        return $out;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $commands = $this->getCommands($input);
        $io = new SymfonyStyle($input, $output);

        if (count($commands) === 0) {
            $io->write('No commands found to document.');
            if (!$input->getOption('include')) {
                $io->write(' You might need to use the <info>--include</info> option to specify a hidden command.');
            }
            $io->newLine();
            $io->writeln('Available commands are:');
            $io->listing(array_map(static function ($cmd) {
                return $cmd->getName();
            }, $this->getApplication()->all()));

            return Command::SUCCESS;
        }

        $commandInfo = '';
        foreach ($commands as $command) {
            $io->writeln('Processing command: ' . $command->getName());
            $description = $command->getDescription() ? $command->getDescription() . "\n\n" : '';
            $commandInfo .= "\n### " . $command->getName() . "\n\n"
                . $description
                . '    ' . $command->getSynopsis() . "\n\n"
                . $this->formatOptions($command->getDefinition()->getOptions())
                . $this->formatArguments($command->getDefinition()->getArguments());
        }

        // Remove local paths.
        $commandInfo = str_replace(getcwd(), '[CWD]', $commandInfo);

        // Write new contents to README.md.
        $readmePath = $input->getOption('readme');
        if (!file_exists($readmePath)) {
            $io->error('README file not found: ' . $readmePath);
            $io->writeln('You can specify a filename with the the <info>--readme</info> option.');
            return Command::FAILURE;
        }
        $readme = file_get_contents($readmePath);
        $usageSection = $input->getOption('usage');
        preg_match("/(.*#+ ?$usageSection\n)(.*)(\n## .*)/s", $readme, $matches);
        if (!isset($matches[1])) {
            $io->error('No "' . $usageSection . '" header found in ' . $readmePath);
            return Command::FAILURE;
        }
        $newReadme = $matches[1] . $commandInfo . $matches[3];
        if ($newReadme !== $readme) {
            file_put_contents($readmePath, $newReadme);
            $io->success('Updated README: ' . $readmePath);
        } else {
            $io->success('No changes required to README: ' . $readmePath);
        }

        return Command::SUCCESS;
    }

    /**
     * @param InputOption[] $options
     */
    private function formatOptions(array $options): string
    {
        $optionsMarkdown = '';
        foreach ($options as $option) {
            // Don't include application options; they're listed separately.
            $isAppOption = $this->getApplication()->getDefinition()->hasOption($option->getName());
            if ($isAppOption) {
                continue;
            }
            $shortcut = $option->getShortcut() ? ' `-' . $option->getShortcut() . '`' : '';
            $default = $option->getDefault() ? 'Default: ' . var_export($option->getDefault(), true) : '';
            $multiple = $option->isArray() ? 'This option can be provided multiple times.' : '';
            $optionsMarkdown .= '* `--' . $option->getName() . "`$shortcut"
                . ' — ' . $option->getDescription() . "\n"
                . ($default . $multiple ? '  ' . $default . $multiple . "\n" : '');
        }
        return $optionsMarkdown;
    }

    /**
     * @param InputArgument[] $arguments
     */
    private function formatArguments(array $arguments): string
    {
        $argumentsMarkdown = '';
        foreach ($arguments as $argument) {
            // Don't include application arguments.
            $isAppArg = $this->getApplication()->getDefinition()->hasArgument($argument->getName());
            if ($isAppArg) {
                continue;
            }
            $argumentsMarkdown .= '* `<' . $argument->getName() . '>` ' . $argument->getDescription() . "\n";
        }
        return $argumentsMarkdown;
    }
}
