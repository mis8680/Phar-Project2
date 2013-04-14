<?php

namespace PharCompiler\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends Command
{
    public function __construct()
    {
        parent::__construct('helloworld');
    }
    
    protected function configure()
    {
        //set the name of the command
        //$this->setName('byworld');
        
        $this->setDefinition(new InputDefinition(array(
            new InputArgument('say',InputArgument::OPTIONAL,'','Hello jeremy and Insu.'),
            new InputOption('insu', 'j', InputOption::VALUE_NONE),
            new InputOption('bye', 'b', InputOption::VALUE_OPTIONAL, '', 'world')
        )));
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Hello World, this is my first console program.");
        $output->writeln('We are saying..."' . $input->getArgument('say') . '"');
        
        if($input->getOption('insu')) {
            $output->writeln('The insu option was specified.');
        }
        
        $output->writeln('Priting opition --bye = ' . $input->getOption('bye'));
        
        $output->writeln(PHP_EOL . PHP_EOL);
    }
    
    
}