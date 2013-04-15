<?php

namespace PharCompiler\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use \Phar;

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
	    ->setDefinition(
		array(
		    new InputArgument('phar_file',null,"this is the name of your future phar file,\nyou can pass absolute or relative path,\nbut don't forget the extension."),
		    new InputArgument('phar_stub',null,"this is the name of your phar stub file,\nthis file will be the excutable stub file that will run \nyour .phar file"),
		    new InputArgument('root_app',null,"this is the root directory of your application\nyou want to turn into phar, you can pass absolute or\nrelative path."),
		)
	    );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
	$output->writeln("\nThe phar_file name is ..." . $input->getArgument('phar_file'));
	$output->writeln('The phar_stub name is ..."' . $input->getArgument('phar_stub') . '"');
	$output->writeln('The root_app is ..."' . $input->getArgument('root_app') . '"');
        
	$this->compress($input->getArgument('phar_file'), $input->getArgument('phar_stub'), $input->getArgument('root_app'));
        
        $output->writeln(PHP_EOL . PHP_EOL);
    }
    
    public function compress($file, $stub, $root)
    {
	//check if the phar file is writable
	$this->is_phar_writable();
	//set the phar file
	$phar = $file;
	//set the phar name
	$phar_name = end(explode('/', $phar));
	//set the phar stub file to be run
	$stub_file = trim($stub);
	//set the root app where phar will be created from
	$root_app = $root;	

	if (!file_exists($root_app)) {
		return $this->error("Root dir of your app doesn't exist.");
	}

	//do not show error message when unlinking $phar
	@unlink($phar);

	//perform compression
	//catch exception thrown and display to user if error occurs
	try {
	    $p = new Phar($phar, Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME, $phar_name);

	    echo "\nCompressing files into: " . $phar_name;
	    echo "\nMaking babies.. \n===================\n";

	    $p->setStub("<?php Phar::mapPhar(); include 'phar://".$phar_name."/".$stub_file."'; __HALT_COMPILER(); ?>");

	    $files = $this->_scandir($root_app);

	    // counter variable to display the number of files that will be added
	    $count = 0;
	    foreach ($files as $file) {
		$file_buff = $file;
		$file = str_replace('\\', '/', $file);
		$file = str_replace($root_app.'/', '', $file);

		//if (!$this->_exclude($file, $shell_masks) && !$this->_exclude($file, array('*/'.$phar_name, $phar_name))) {
		    $p[$file] = file_get_contents($file_buff);
		    echo "adding $file ..\n";
		    $count++;
		//}
	    }

	    echo "\nTotal: $count files added\n";

	    return $this->success("CREATED $phar.. thank you for using:
 _______    _____      _   __	  _   __        _
|__   __|  / ___ \    | | / /    | | / /       / \
   | |	  / /   \ \   | |/ /     | |/ /       / / \
   | |   | |    | |   | | /      | | /       / /_\ \
   | |   | |    | |   | |\ \     | |\ \     / _____ \
 __| |   \ \___/ /    | | \ \    | | \ \   / /     \ \
|____/    \_____/     |_|  \_\   |_|  \_\ /_/       \_\
(c) Jeremy Mills <jeremy.mills89@gmail.com>
(c) Insu Mun <mis8680@gmail.com>
(c) Carlie Hiel <carlie.hiel@gmail.com>
Jokka is a complete symfony based phar compiling tool.
Jokka is built with the help of (c) Jeremy Perret's <jeremy@devstar.org> Empir php compiling tool.
phar.readonly must be set to 0 within your php.ini in order to run");

	    $phar_copy = $phar.'phar';
	    @unlink($phar_copy);
	    $p = $p->convertToExecutable(Phar::PHAR, Phar::NONE);
	    $this->success("CREATE $phar_copy");
	    @unlink($phar);

	} catch (Exception $e) {
		return $this->error($e->getMessage());
	}
    }
    
    /**
     *
     */
    private function is_phar_writable()
    {
	if (!Phar::canWrite()) {
	    exit($this->error(
		"Unable to write phar, phar.readonly must be set to zero (0) in your php.ini..")
	    );
	}
    }
    
    
    
    /**
     *
     */
    protected function error($message = '', $errno = 1)
    {
	if ($message != '') {
	    //create a new output instance variable
	    $output = new ConsoleOutput();

	    $output->writeln("<error>ERROR: $message\n</error>");
	}
	return $errno;
    }

    /**
     *
     */
    protected function success($message)
    {
	//create a new output instance variable
	$output = new ConsoleOutput();

	$output->writeln("<info>$message\n</info>");
    }
    
    /**
     *
     */
    private function _scandir($path)
    {
	$items = array();
	$path = rtrim($path, '/');

	if (!$current_dir = opendir($path)) {
		return $items;			
	}

	while (false !== ($filename = readdir($current_dir))) {
		if ($filename != "." && $filename != "..") {
			if (is_dir($path.'/'.$filename)) {
				$items = array_merge($items, $this->_scandir($path.'/'.$filename));	
			} else {
				$items[] = $path.'/'.$filename;
			}
		}
	}
	// close current directory
	closedir($current_dir);
	// return subdirectory items
	return $items;
    }
}