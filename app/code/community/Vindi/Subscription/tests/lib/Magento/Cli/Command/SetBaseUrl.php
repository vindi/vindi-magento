<?php

namespace Magium\Magento\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetBaseUrl extends Command
{

    protected function configure()
    {
        $this->setName('magento:base-url');
        $this->setDescription('Sets the Base URL for a given theme');
        $this->addArgument(
            'url',
            InputArgument::REQUIRED,
            'The URL to set'
        );
        $this->addArgument(
            'theme',
            InputArgument::OPTIONAL,
            'The theme to set the URL for.  Defaults to Magento19 (Magento CE 1.9).  Non FQ classes will be resolved using internal mechanisms'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $command = $this->getApplication()->find('element:set');

        $class = $input->getArgument('theme');
        if (!$class) {
            $class = 'Magento19';
        }

        $resolver = new ThemeResolver();
        $class = $resolver->resolve($class);

        $internalInput = new ArrayInput([
            'command'   => $command->getName(),
            'class'     => $class,
            'property'  => 'baseUrl',
            'value'     => $input->getArgument('url')
        ]);

        $command->run($internalInput, $output);

    }

}