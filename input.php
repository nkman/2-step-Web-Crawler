<?php
//error_reporting(E_ALL);
$host = 'localhost';
$user = 'root';
$db = 'sample';
$pwd = '******';
require('phpQuery/phpQuery.php');
$connection = mysql_connect($host,$user,$pwd) or die("unable to connect to database");
mysql_select_db($db) or die("Unable to select database");

$url = 'http://www.songspk.name' ;										   //Url can be editable.
$s = file_get_contents($url);
$links = phpQuery::newDocument($s)->find('a');
$array_links = array();
foreach($links as $r)
{
$array_links[] = pq($r)->attr('href');
}
$size1 = sizeof($array_links);                                             //This array contains all the http links
																		   //and size1 contains the size of array_links.
function validate($link,$url)											   //It will check weather the link is valid or not.	
{

	//echo $link[3];
	if ($link[0] == 'h') {													//If link starts with http then return it as it is.
		return $link;
	}
	elseif (ord($link[0]) > "96" && ord($link[0]) < "123") {				//check if link starts from a -> z.
		return $url.'/'.$link;												//return the merged link :).
	}
	elseif ($link[0] == '/') {
		return $url.$link;													//return the merged link.
	}
	else {
		return "null";														//not a valid link
	}
}
$p = mysql_num_rows(mysql_query("SELECT * FROM Entry"));

$i = 0;
while($i < $size1)															//to insert the link in db.
{	
	$data = validate($array_links[$i],$url);
	if($data!="null")							//check weather the returned value in not null
	{
		$o = 0;					
		//print $data;
		$results = mysql_query("SELECT * FROM Entry");   					//this 2 line to get the total size of db
		$num_rows = mysql_num_rows($results);
		for($o;$o<$num_rows;$o++)                   					//this is for finding the matches.
		{
			
			$e = mysql_query("SELECT `link` FROM `Entry` WHERE `No`=$o");    //selecting link
			while($row = mysql_fetch_row($e))
			{$valueoflink = $row[0];}                                			//storing link value in $row
			if (strcmp($valueoflink,$data)=="0") 						    //Comparing if both are equal.
			{break;}															//break if equal now $o is not equal to num_row
															
		}
		if($o==$num_rows) 												//if no match found.
		{
		$query = "INSERT INTO `Entry`(`No`, `link`) VALUES ('$p','$data')"; //insert the link in db
		$result = mysql_query($query) or die ("Unable To Process Query");	//
		$p++;
		}

	}
	$i++;
}

//<! -----finding mp3 now ---!>//
$i = 0;
while ($i < $p) {																//now we'll check for .mp3 in each link.
	
$e = mysql_query("SELECT `link` FROM `Entry` WHERE `No`=$i");    //selecting link
while($row = mysql_fetch_row($e))
{$checkurl = $row[0];} 											 //Defining the value to checkurl in which we will peep for mp3.
$q = file_get_contents($checkurl);
$links = phpQuery::newDocument($q)->find('a');
$array_links_mp3 = array();
foreach($links as $r)
{
$array_links_mp3[] = pq($r)->attr('href');							//array_link_mp3 contains the links in $checkurl.
}
for($c=0;$c<sizeof($array_links_mp3);$c++)							//to check all the mp3.
{
	$actuallink = $array_links_mp3[$c];								//this is a link
	$sizeoflink = strlen($actuallink);								//length of the link.
	$extension = $actuallink[$sizeoflink-3].$actuallink[$sizeoflink-2].$actuallink[$sizeoflink-1];    //the last extension.
	//echo $extension;
	if($extension == "mp3")											//suppose the extension is mp3
	{
		$num = mysql_num_rows(mysql_query("SELECT * FROM mpthree"));
		$resultnow = mysql_query("INSERT INTO `sample`.`mpthree` (`no`, `link`) VALUES ('$num', '$actuallink')");
																	//storing mp3 in table named mpthree
	}
}
$i++;
}


/* now we will write this in web page */

echo "<html><title>nkman</title><body>";
$show = mysql_query("SELECT * FROM mpthree");
while ($showrow=mysql_fetch_row($show)) {
	echo $showrow[1]."</br>";
}
echo "</body></html>";
mysql_free_result($result);

mysql_close($connection);
 ?>
