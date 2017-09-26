<?php

# I will turn this into a class

# http://dev.maxmind.com/geoip/geoip2/geolite2/
//echo "Hello";
//echo "Loading the file";

# The databases
global $geoloc_dbs;

//$geoloc_currentLocation = '/home/lasangha/public_html/subDomainZ/geoloc/';
$geoloc_currentLocation = dirname(__FILE__);

geoloc_initMe($geoloc_currentLocation);

function geoloc_initMe($path){

	global $geoloc_dbs;

	$geoloc_dbs['dbB'] = new SQLite3($path . 'blocks.sqlite');
	$geoloc_dbs['dbL'] = new SQLite3($path . 'locations.sqlite');

}
# Uncomment this in order to create the databases
//createTables();

# Uncomment this in order to load each database DON'T DO THIS FROM WEB, IT WILL TAKE A WHILE!!!
//geoloc_loadBlocks();
//geoloc_loadLocations();

// This is just for testing, you probably will never use it
//geoloc_getMeVisitorDetails();

function geoloc_createTables(){

	global $geoloc_dbs;

	echo "Creating tables\n";

	$geoloc_dbs['dbB']->exec('DROP TABLE IF EXISTS "blocks";');
	$geoloc_dbs['dbB']->exec('CREATE TABLE "blocks" ("startIpNum" INTEGER NOT NULL , "endIpNum" INTEGER NOT NULL , "locId" INTEGER);');
	$geoloc_dbs['dbL']->exec('DROP TABLE IF EXISTS "locations";');
	$geoloc_dbs['dbL']->exec('CREATE TABLE "locations" ("locId" INTEGER PRIMARY KEY  NOT NULL , "country" VARCHAR, "region" VARCHAR, "city" VARCHAR, "postalCode" VARCHAR, "latitude" VARCHAR, "longitude" VARCHAR, "metroCode" VARCHAR, "areaCode" VARCHAR)');

}

function geoloc_loadLocations(){

	global $geoloc_dbs;

	echo "Loading locations \n";

	$fileCities = file("./GeoLiteCity-Location.csv");

	$c = 0;
	$cc = 0;
	foreach($fileCities as $f){

		# Skip first two
		if($cc > 1){
			# Fix missing values
			$ff = explode(",", $f);
			for($a = 0; $a < 9; $a++){
				if(!isset($ff[$a]) || $ff[$a] == NULL || $ff[$a] == "\n"){
					//echo "missing!$a"; //	= 0;
					$ff[$a] = 0;
				}
			}

			$f = implode(",", $ff);

			if($c == 0){
				$q = "insert into locations (locId,country,region,city,postalCode,latitude,longitude,metroCode,areaCode) VALUES ";
				$q2 = array();
			}
			echo "+";
			$q2[] = "(" . trim($f, "\n") . ")";

			$c++;
			if($c == 500){
				$query = $q . implode(",", $q2) . ";";
				echo "\n.-";
				$geoloc_dbs['dbL']->exec($query);
				$c = 0;
				echo $cc;
			}
 
		}
		$cc++;
	}

}

function geoloc_loadBlocks(){

	global $geoloc_dbs;

	$fileCities = file("./GeoLiteCity-Blocks.csv");

	$c = 0;
	$cc = 0;
	foreach($fileCities as $f){
		if($c == 0){
			$q = "insert into blocks (startIpNum, endIpNum, locId) VALUES "; //" . trim($f, "\n") . ");";
			$q2 = array();
		}
		echo "+";
		$q2[] = "(" . trim($f, "\n") . ")";

		$c++;
		$cc++;
		if($c == 500){
			$query = $q . implode(",", $q2) . ";";
			echo ".-";
			$geoloc_dbs['db']->exec($query);
			$c = 0;
			echo $cc;
		}

	}

}
// Temp for random generation of locations
function geoloc_getMeVisitorDetails($ipAddress = ""){

	global $geoloc_dbs;

	$blocks['locId'] = rand(0, 1000);

	if($blocks){
		$q = sprintf("
			SELECT *
			FROM `locations`
			WHERE locId = %s
			", $blocks['locId']);
	
		$stmt = $geoloc_dbs['dbL']->prepare($q);
		$result = $stmt->execute();
		$details = $result->fetchArray();
	}

	return $details;
}

function _geoloc_getMeVisitorDetails($ipAddress = ""){

	global $geoloc_dbs;

	# I will use the address of the current visitor if nothing is provided
	if($ipAddress == ""){
		$ipAddress = $_SERVER['REMOTE_ADDR'];
	}

	$ipParts = explode(".", $ipAddress);

	$integerIp = (16777216 * $ipParts[0])
		+ (    65536 * $ipParts[1])
		+ (      256 * $ipParts[2])
		+              $ipParts[3];

	# Get the block

	# Add all the details in blank
	# @todo add them all in blank
	$details = array();

	// From  http://dev.maxmind.com/geoip/legacy/geolite/
	$q = sprintf("
		SELECT b.locId
		FROM `blocks` b
		WHERE startIpNum <= %s 
		AND endIpNum >= %s
		", $integerIp, $integerIp);

	$stmt = $geoloc_dbs['dbB']->prepare($q);

	$result = $stmt->execute();
	$blocks = $result->fetchArray();

	if($blocks){
		$q = sprintf("
			SELECT *
			FROM `locations`
			WHERE locId = %s
			", $blocks['locId']);
	
		$stmt = $geoloc_dbs['dbL']->prepare($q);
		$result = $stmt->execute();
		$details = $result->fetchArray();
	}

	return $details;

}


