<?php
	require_once dirname(dirname(__FILE__))."/src/pbalan/DirectoryParser/DirectoryParser.php";
	use pbalan\DirectoryParser;
	//echo "wga"; exit;
	$dest = dirname(__FILE__).'/upload';
	$dirObj = new pbalan\DirectoryParser\DirectoryParser();
	$dirObj->createDirectory($dest);
	$return = $dirObj->getFileList($dest);
	
	var_dump($return);