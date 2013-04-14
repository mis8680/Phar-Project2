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
                new InputArgument('phar_file',null,"This is the name of your future phar file,\nyou can pass absolute or relative path,\nbut don't forget the extension."),
                new InputArgument('root_app',null,"This is the root directory of your application\nyou want to turn into phar, you can pass absolute or\nrelative path."),
             ))
	    
	    
	;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
	//check if the phar file is writable
	$this->is_phar_writable();
	//set the phar file
	$phar = $this->makeAbsolute($this->request_option(0, 'phar filename'));
	//set the phar name
	$phar_name = end(explode('/', $phar));
	//set the phar stub file to be run
	$stub_file = trim($this->request_option(1, 'stub file'), '/');
	//set the root app where phar will be created from
	$root_app = $this->makeAbsolute($this->request_option(2, 'root dir of your app'));
	if (!file_exists($root_app)) {
			return $this->error("Root dir of your app doesn't exist.");
		}
	
	
	
        $output->writeln("Hello World, this is my first console program.");
        $output->writeln('The phar file name is ..."' . $input->getArgument('phar_file') . '"');
	$output->writeln('The root app is ..."' . $input->getArgument('root_app') . '"');
              
        $output->writeln(PHP_EOL . PHP_EOL);
    }
    
    protected function is_phar_writable()
    {
		if (!Phar::canWrite()) {
			exit($this->error(
				"Unable to write phar, phar.readonly must be set to zero in your php.ini otherwise use:
				$ php -dphar.readonly=0 empir <command> ...")
			);
	    }
    }
    
    protected function makeAbsolute($path = '')
    {
		$current = getcwd().'/';
		if ($path === "" || $path === false) {
			$absolut_path = $current;
		} elseif (substr($path, 0, 2) == './') {
			$absolut_path = $current.substr($path, 2);
		} elseif (strpos($path, ':') === 1 || substr($path, 0, 2) == '\\\\' || substr($path, 0, 1) == '/') {
			$absolut_path = $path;
		} else {
			$absolut_path = $current.$path;
		}

		$absolut_path = str_replace('\\', '/', $absolut_path);
		$absolut_path = rtrim($absolut_path, '/');

		return $absolut_path;
    }
    protected function get_option($no)
    {
		if (isset($this->options[$no])) {
			return $this->options[$no];
		}
		return null;
    }

    protected function request_option($no, $name)
    {
	if ($this->get_option($no) == null) {
			exit($this->error("Param $name is required. Try <help>"));
		}
		return $this->get_option($no);
    }
    
    protected function error($message = '', $errno = 1)
    {
	if ($message != '') {
			echo Color::str("ERROR: $message\n", Empir::ERR_COLOR);
		}
		return $errno;
    }
    
    
}