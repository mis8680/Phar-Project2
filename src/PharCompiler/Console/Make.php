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
	//set the compression type
	//$_compression = 'no';
	//set the format to compress on (phar)
	//$_format = 'phar';

	if (!file_exists($root_app)) {
		return $this->error("Root dir of your app doesn't exist.");
	}

	/*if (!empty($_compression) && !in_array($_compression, $this->compression_types)) {
		return $this->error("Unrecognized compression: $_compression");
	}
	if (!empty($_format) && !in_array($_format, $this->format_types)) {
		return $this->error("Unrecognized format: $_format");
	}

	if (!empty($_fexclude)) {
		$_fexclude = $this->makeAbsolute($_fexclude);
		if (!file_exists($_fexclude)) {
			return $this->error("Exclude file: $_fexclude not found.");
		}
		$_fexclude = file_get_contents($_fexclude);
	}

	$shell_masks = explode('|', $_exclude);
	$shell_masks = array_merge($shell_masks, explode("\n", $_fexclude));*/

	//do not show error message when unlinking $phar
	@unlink($phar);

	//perform compression
	//catch exception thrown and display to user if error occurs
	try {
	    $p = new Phar($phar, Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME, $phar_name);

	    echo "\nCompressing files into: ".$phar_name;
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
    private function get_var($var)
    {
	if (is_string($var)) {
	    if (isset($this->$var)) {
		return $this->$var;
	    }
	} else {
	    foreach (array_merge($this->compression_types, $this->format_types) as $v) {
		if ($this->$v->int_value == $var) {
		    return $this->$v;
		}
	    }
	}
    }
    
    /**
     *
     */
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
    
    /**
     *
     */
    protected function execCommand()
    {
	if (isset($this->commands[$this->command])) {
            $method = $this->commands[$this->command];
	    $rcode = $this->$method();
	} else {
	    $rcode = $this->error("Command <$this->command> doesn't exist. Try -h");
        }
	$this->
        return ($rcode == null) ? 0 : $rcode;
    }

    /**
     *
     */
    protected function get_option($no)
    {
    	if (isset($this->options[$no])) {
    	    return $this->options[$no];
    	}
    	return null;
    }

    /**
     *
     */
    protected function get_last_option($opt)
    {
    	foreach ($this->options as $option) {
    	    if (strpos($option, "--$opt=") !== false) {
    		return trim(end(explode('=', $option)), '"');
    	    }
    	}
    	return null;
    }
    
    /**
     *
     */
    protected function request_option($no, $name)
    {
    	if ($this->get_option($no) == null) {
    	    exit($this->error("Param $name is required. Try -h"));
    	}
	return $this->get_option($no);
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