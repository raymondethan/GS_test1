<?php

	$domains = array(
            "gs" => "local",
            "api.gs" => "local",
            "development.globalstories.org" => "dev",
			"www.globalstories.org" => "live",
			"globalstories.org" => "live",				
            "api.development.globalstories.org" => "live",
	);
        
	$platform = "local";
	
	if(isset($domains[$_SERVER['HTTP_HOST']])){
    		$platform = $domains[$_SERVER['HTTP_HOST']];
	}

	switch ($platform){
            case "local": $config_file = 'config-local.php'; break;
            case "dev": $config_file = 'config-dev.php'; break;	
			case "live": $config_file = 'config-live.php'; break;	
	}
?>
