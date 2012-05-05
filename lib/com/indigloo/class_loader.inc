<?php
    
	function WebglooLoader($className) {

		//replace double slashes in class name (PHP 5.3 namespace convention)
		// with a forrward slash (or PATH_SEPARATOR on your box)
		$file = str_replace('\\', '/', $className) . '.php';

		//try loading from APP_LIB_PATH first
		$libpaths = $_SERVER['APP_LIB_PATH'];
		foreach($libpaths as $libpath) {
			$path = $libpath.'/'.$file; 
			
			if (file_exists($path) && is_readable($path)) {
				include_once $path;
				return;
			}
		}
	}

    spl_autoload_register("WebglooLoader");
?>
