<?php
	/*	DirectoryParser is a class that provides PHP actions inside a directory/folder
	 *	@Author: Prashant Balan
	 **/
	
	namespace pbalan\DirectoryParser;
	
	class DirectoryParser{
		private $dir = null;
		private $allowedExts = array('jpg','jpeg','gif','png');
		private $recurse = false;
		private $recurseCreate = false;
		private $relative = null;
		
		public function __construct($dir=null, $allowedExts='')
		{
			$this->dir = $dir;
			if(false===empty($allowedExts) && true===is_array($allowedExts))
			{
				$this->allowedExts = $allowedExts;
			}
		}
        public function getCurrentDirectory()
        {
            return $this->dir;
        }
        public function setCurrentDirectory($dir)
        {
            if(true===is_dir($dir)){
                $this->dir = $dir;
            }
            return $this->dir;
        }
		public function getFileList($dir='', $exts='', $recurse='', $filename='', $dirOnly=false)
		{
			// array to hold return value
			$retval = array();
            $dirval = array();
            if(false===empty($dir) && true===is_dir($dir))
            {
                $this->dir = $dir;
            }
            
			if(false===empty($exts) && true===is_array($exts))
			{
				$this->allowedExts = $exts;
			}
			if(true===is_bool($recurse))
			{
				$this->recurse = $recurse;
			}
			
            $this->checkDirectoryFlow();
            //echo "dir: ".$this->dir.", dirOnly: ".$dirOnly."<br/>";
			// open pointer to directory and read list of files
			$d = @dir($this->dir) or die("getFileList: Failed opening directory ".$this->dir."for reading");
			while(false !== ($entry = $d->read())) 
			{
				// skip hidden files
				if($entry[0] == ".") continue;
                if(is_dir($this->dir.$entry)) 
                {
                    if(in_array($this->fileexts($this->dir.$entry),$this->allowedExts))
                    {
                        if(false==empty($filename) && false!==stripos($this->dir.$entry,$filename))
                        {
                            return $this->dir.$entry;//files only
                        } else {
                            $retval[] = $this->dir.$entry; //files only
                        }
                    } else if(true===is_bool($dirOnly) && true===$dirOnly && true===is_dir($this->dir."$entry/")){
                        $dirval[] = $this->dir."$entry";
                    }
                    if(true===$this->recurse && true===is_readable($this->dir."$entry/")) 
                    {
                        $retval = array_merge($retval, $this->getFileList($this->dir."$entry/", $this->allowedExts, $this->recurse, $filename));
                    }
                } 
                elseif(true===is_readable($this->dir."$entry")) 
                {
                    if(true===in_array($this->fileexts($this->dir.$entry),$this->allowedExts))
                    {
                        if(false==empty($filename) && false!==stripos($this->dir.$entry,$filename))
                        {
                            return $this->dir.$entry; //files only
                        } else if(true===is_bool($dirOnly) && true===$dirOnly && true===is_dir($this->dir.$entry)){
                            $dirval[] = $this->dir.$entry;
                        } else {
                            $retval[] = $this->dir.$entry;//files only
                        }
                    }
                }
			}
            $d->close();
            if(true===is_bool($dirOnly) && true===$dirOnly)
            {
                return $dirval;
            } else {
                return $retval;
            }
		}
		
        public function searchFile($filename, $dir='', $allowedExts='', $recurse=false){
            $return = '';
            if(false===empty($filename))
            {
                if(false===empty($dir))
                {
                    if(true===is_dir($dir))
                    {
                        $this->dir = $dir;
                    } else {
                        echo "Directory $dir does not exist."; exit;
                    }
                }
                if(false===empty($allowedExts) && true===is_array($allowedExts))
                {
                    $this->allowedExts = $allowedExts;
                }
                $this->checkDirectoryFlow();
                
                $return = $this->getFileList($this->dir, $this->allowedExts, $recurse, $filename);
            }
            else 
            {
                echo "Please provide a File name to search"; exit;
            }
            return $return;
        }
        
		public function fileexts($filename)
		{
			$extension = explode('.',$filename);
			$extension = end($extension);
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
			$this->checkDirectoryFlow();
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
		
		public function checkDirectoryFlow($dir='')
		{
            if(false===empty($dir))
            {
                $dir = str_replace('\\','/',$dir);
                if(substr($dir, (strlen($dir)-1))!='/')
                {
                    $dir .= '/';
                }
                return $dir;
            } else {
                $this->dir = str_replace('\\','/',$this->dir);
                if(substr($this->dir, (strlen($this->dir)-1))!='/')
                {
                    $this->dir .= '/';
                }
                return true;
            }
		}
		
		public function addRelativeDirectory($relative)
		{
			$this->relative = $relative;
			$this->checkDirectoryFlow();
            echo $this->dir.$this->relative."<br/>";
			if(false===is_dir($this->dir.$this->relative))
			{
				return $this->createDirectory($this->dir.$this->relative, 0777, true);
			}
			
		}
		
		/*	Copy a file from source directory to destination directory
		 *	@param	source:			directory to copy from
		 *	@param	destination:	directory to copy to
		 */
		public function copyToDirectory($source, $destination)
		{
			//check to make sure the file exists
			if(file_exists($source))
			{
				copy($source,$destination);
			}
			//check to make sure the file exists
			if(file_exists($destination))
			{
				return true;
			} 
			else 
			{
				return false;
			}
		}
        
        public function createFile($filename, $dir='', $content='')
        {
            $path = '';
            if(false===empty($filename))
            {
                if(false===empty($dir))
                {
                    if(true===is_dir($dir))
                    {
                        $this->dir = $dir;
                    }
                }
                $this->checkDirectoryFlow();
                $path = $this->dir.$filename;
                $fh = fopen($path, 'w+');
                if(false===empty($content))
                {
                    try{
                        fwrite($fh, $content);
                    }
                    catch(Exception $e)
                    {
                        echo $e->Message;
                    }
                }
                fclose($fh);
            }
            return $path;
        }
        
        public function readFile($filename)
        {
            $content = '';
            if(false===empty($filename) && true===file_exists($filename))
            {
                $fh = fopen($filename, 'r');
                $content = fread($fh, filesize($filename));
                fclose($fh);
            }
            return $content;
        }
        
        public function listDirectories($dir='')
        {
            $dirList = '';
            if(false===empty($dir) && true===is_dir($dir))
            {
                $dirList = $this->getFileList($dir, $this->allowedExts, false, '', true);
            }
            
            return $dirList;
        }
        
        public function moveToDir($source, $destination)
        {
            if(false===empty($source) && false===empty($destination))
            {
                rename($source, $destination);
            }
        }
	}
?>