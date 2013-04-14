<?php

namespace PharCompiler\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Make extends Command
{
    public function __construct()
    {
        parent::__construct();
    }
    
    protected function configure()
    {
        //set the name of the command
        $this
	    ->setName('make')
	    ->setDescription('Create new .phar file')
	    ->setDefinition(array(
                new InputArgument('phar_file',null,"this is the name of your future phar file,\nyou can pass absolute or relative path,\nbut don't forget the extension."),
                new InputArgument('root_app',null,"this is the root directory of your application\nyou want to turn into phar, you can pass absolute or\nrelative path."),
             ))
	    
	    
	;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Hello World, this is my first console program.");
        $output->writeln('The phar file name is ..."' . $input->getArgument('phar_file') . '"');
	$output->writeln('The root app is ..."' . $input->getArgument('root_app') . '"');
        
        
        
        $output->writeln(PHP_EOL . PHP_EOL);
    }
    
    
}