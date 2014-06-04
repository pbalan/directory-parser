<?php
	/*	DirectoryParser is a class that provides PHP actions inside a directory/folder
	 *	@Author: Prashant Balan
	 **/
	
	namespace pbalan\DirectoryParser;
	
	class DirectoryParser(){
		private $dir = null;
		private $allowedExts = array('jpg','jpeg','gif','png');
		private $recurse = false;
		
		public function __construct($dir='', $allowedExts='')
		{
			if(false===empty($dir) && true===is_dir($dir))
			{
				$this->dir = $dir;
			}
			if(false===empty($allowedExts) && true===is_array($allowedExts))
			{
				$this->allowedExts = $allowedExts;
			}
		}
		
		function getFileList($dir='', $exts='', $recurse='')
		{
			// array to hold return value
			$retval = array();
			if(false===empty($exts) && true===is_array($exts))
			{
				$this->allowedExts = $exts;
			}
			if(true===is_bool($recurse))
			{
				$this->recurse = $recurse;
			}
			// add trailing slash if missing
			if(substr($this->dir, -1) != "/")
			{
				$this->dir .= "/";
			}
			// open pointer to directory and read list of files
			$d = @dir($this->dir) or die("getFileList: Failed opening directory ".$this->dir."for reading");
			while(false !== ($entry = $d->read())) 
			{
				// skip hidden files
				if($entry[0] == ".") continue;
				if(is_dir($this->dir.$entry)) 
				{
					if(in_array(fileexts($this->dir.$entry),$this->allowedExts))
					{
						$retval[] = $this->dir.$entry; //files only
					}
					if(true===$this->recurse && true===is_readable($this->dir."$entry/")) 
					{
						$retval = array_merge($retval, $this->getFileList($this->dir."$entry/", true));
					}
				} 
				elseif(true===is_readable($this->dir."$entry")) 
				{
					if(true===in_array($this->fileexts($this->dir.$entry),$this->allowedExts))
					{
						$retval[] = $this->dir.$entry;//files only
					}
				}
				$d->close();
				
				return $retval;
			}
		}
		
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
				$this->dir = $_SERVER['DOCUMENT_ROOT'];
			}
			
			if(true===is_bool($recurse) && false===empty($recurse)){
				$this->recurseCreate = $recurse;
			}
			
			if(false===is_dir($this->dir))
			{
				$directory = mkdir ( $this->dir , $mkdirMode, $this->recurseCreate);
			}
			//check to make sure the directory exists
			if(true===is_dir($this->dir))
			{
				chmod($this->dir, 0777);	// explicity change directory mode
			}
			
			return $this->dir;
		}
	}
?>