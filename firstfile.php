<?Php
	//Find the first file in a folder
//$extensions=array('mkv');
	function firstfile($dir,$extensions)
	{
		if(!is_dir($dir))
		{
			return false;
		}
		if(substr($dir,-1,1)==='/')
			$dir=substr($dir,0,-1);
		foreach(scandir($dir) as $file)
		{
			if($file[0]==='.')
				continue;
			$pathinfo=pathinfo($dir.'/'.$file);
			
			if(is_dir($dir.'/'.$file)) //Check if there is something in subdir
			{
				$result=firstfile($dir.'/'.$file,$extensions);
				if(!empty($result))
					return $result;
			}
			else
			{
				if(array_search($pathinfo['extension'],$extensions)!==false)
					return $dir.'/'.$file;
			}
		}
			
	}
//var_Dump(firstfile($argv[1],$extensions));