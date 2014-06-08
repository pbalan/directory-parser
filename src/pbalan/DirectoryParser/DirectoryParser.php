<?php
	/*	DirectoryParser is a class that provides PHP actions inside a directory/folder
	 *	@Author: Prashant Balan
	 **/
	
	namespace pbalan\DirectoryParser;
	
	// REVIEW in general in the class it is difficult to know why you instanciate the value of dir in the 
	// constructor qnd then keep passing it as a parameter to the functions
	// it would more useful to add a root directory in the constructor and pass the directory
	// that you want to parse in the functions, you could default to '.' to use the current root directory
	// You should put more PHPDoc on your function comment.
	class DirectoryParser{
		private $dir = null;
		private $allowedExts = array('jpg','jpeg','gif','png');
		private $recurse = false;
		private $recurseCreate = false;
		
		public function __construct($dir='', $allowedExts='')
		{
			if(false===empty($dir) && true===is_dir($dir))
			{
				$this->dir = $dir;
			}
			
		        // REVIEW you can force the parameter to be an array
		        // array $allowedExts = array(), no need to vqlidqte type parameters
			if(false===empty($allowedExts) && true===is_array($allowedExts))
			{
				$this->allowedExts = $allowedExts;
			}
		}
		
		// REVIEW always take the habit to put the visibility public, private or protected
		function getFileList($dir='', $exts='', $recurse='')
		{
			// array to hold return value
			$retval = array();
			// REVIEW same you can just check that the array is empty, force the user to pass an array
			if(false===empty($exts) && true===is_array($exts))
			{
				$this->allowedExts = $exts;
			}
			// REVIEW is is not good to use the type of the variable to decide on some action
			// you could just pass $recurse = false; if you are doing this not to erase the default
			// value of $this->recurse, I would suggest to pass an array of options $options, and
			// check for if the parameter exist (isset($options['recurse']) to decide wether to use
			// the default value or the option one, besides it makes it easier if later you want to 
			// add options. You could use the same idea for the $exts variable.
			if(true===is_bool($recurse))
			{
				$this->recurse = $recurse;
			}
			// add trailing slash if missing
			if(substr($this->dir, -1) != "/")
			{
				$this->dir .= "/";
			}
			// REVIEW don't die, use exception, try catch (and finally if php 5.5+)
			// open pointer to directory and read list of files
			$d = @dir($this->dir) or die("getFileList: Failed opening directory ".$this->dir."for reading");
			while(false !== ($entry = $d->read())) 
			{   
			        // REVIEW good to put comments on that!! Though be careful with the non-strict equality check
			        // it can lead to weird behaviors http://us3.php.net/manual/en/types.comparisons.php
				// skip hidden files
				if($entry[0] == ".") continue;
				if(is_dir($this->dir.$entry)) 
				{
					// REVIEW where does fileexts co,es from ? you forgot the $this?
					// REVIEW put comments on the behavior of this code
					if(in_array(fileexts($this->dir.$entry),$this->allowedExts))
					{
						$retval[] = $this->dir.$entry; //files only
					}
					// REVIEW put comments on the behavior of this code
					if(true===$this->recurse && true===is_readable($this->dir."$entry/")) 
					{
						$retval = array_merge($retval, $this->getFileList($this->dir."$entry/", true));
					}
				} 
     				// REVIEW put comments on the behavior of this code
				elseif(true===is_readable($this->dir."$entry")) 
				{
					// you could put this in the same condition as the previous control stqtement
					if(true===in_array($this->fileexts($this->dir.$entry),$this->allowedExts))
					{
						$retval[] = $this->dir.$entry;//files only
					}
				}
				$d->close();
				
				return $retval;
			}
		}
		
		// REVIEW you could use pathinfo http://php.net/manual/en/function.pathinfo.php instead of
	        // this function, always check if a php function exists for this kind of common behavior
		public function fileexts($filename)
		{
			$extension = explode('.',$filename);
			$extension = end($extension);
			//	$cnt = count($extension);
			//	$extension = $extension[$cnt-1];
			return $extension;
		}
		
		public function createDirectory($dir='', $mkdirMode=0777, $recurse=false){
			if(false===empty($dir))
			{
				$this->dir = $dir;
			} 
			else 
			{
				// REVIEW *never* use  $_SERVER['DOCUMENT_ROOT'] like that, what if you are using the function
				// in cli mode ? Then this would fail badly.
				$this->dir = $_SERVER['DOCUMENT_ROOT'];
			}
			
		        // REVIEW now recurs is a boolean by default!! Keep the behavior consistent across functions
			if(true===is_bool($recurse) && false===empty($recurse)){
				$this->recurseCreate = $recurse;
			}
			
			if(false===is_dir($this->dir))
			{
				// REVIEW use exception if mkdir fails
				$directory = mkdir ( $this->dir , $mkdirMode, $this->recurseCreate);
			}
			// REVIEW why not use else here ? 
			//check to make sure the directory exists
			if(true===is_dir($this->dir))
			{
				// REVIEW use exception if mkdchmodir fails, you are not using $mkdirMode?
				chmod($this->dir, 0777);	// explicity change directory mode
			}
			
			// REVIEW return something useful, a success/error value although I prefer exception,
			// or return $this, the instance of the directory parser, this way you can use this class as a 
			// fluent interface http://en.wikipedia.org/wiki/Fluent_interface
			return $this->dir;
		}
	}
?>
