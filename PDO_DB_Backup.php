<?php
/* @script name 	PHP PDO Database Back Up Tool
 * @description 	A PHP Script File that will back up your database tables using PDO
 * @author			Kel Novi
 * @copyright		Kel Novi
 * @website			http://kelnovi.ml/
 * @license			http://opensource.org/licenses/MIT	MIT License
 */
define('ROOT_DIR', 	realpath(dirname(__FILE__)) .'/');

class PDO_DB_Backup {

	private $dbc;
	private $dbname;
	private $str;

	/**
	 * Connect to a database
	 *
	 * @param	array
	 * @return	void
	 */

	public function __construct($config){

		$this->dbc 		= new PDO("mysql:host=".$config[0].';dbname='.$config[3],$config[1],$config[2]);
		$this->dbname 	= $config[3];

	}


	/**
	 * generate the .sql file to a folder named 'BACK_UP_DB'
	 *
	 * @param	$filename - The name of sql file 
	 * @return	string - the generated filename of the sql file
	 */
	public function generateBackUp($filename = NULL){

		$tables = array();
		$result = $this->dbc->query('SHOW TABLES');
		$tables = $result->fetchAll(PDO::FETCH_ASSOC);
		
		$this->str .="--PDO DB Back up SQL Dump
		-- version 1.0.0
		-- http://kelnovi.ml
		-- Host: ".$_SERVER['HTTP_HOST']."
		-- Generation Time: ".date('Y-m-d h:i:s a')."
		-- Server Info:  ".$_SERVER['SERVER_SOFTWARE'];

		foreach($tables as $data){

			$result = $this->dbc->query("SELECT * FROM ".$data["Tables_in_".$this->dbname]);
			$result->fetchAll(PDO::FETCH_ASSOC);
			$num_fields = $result->columnCount();

			$result = $this->dbc->query("SHOW CREATE TABLE ".$data["Tables_in_".$this->dbname]);
			$tablequeries = $result->fetchAll(PDO::FETCH_ASSOC);


		
			$this->str .=  "\n\n".($tablequeries[0]['Create Table']).";  \n \n";

			$result2 = $this->dbc->query("SELECT * FROM ".$data["Tables_in_".$this->dbname]);
			$allTableSqlQueries = $result2->fetchAll(PDO::FETCH_ASSOC);

			for($i=0; $i < $result2->columnCount();$i++){

				foreach($allTableSqlQueries as $row){

					$this->str.= "INSERT INTO ".$data["Tables_in_".$this->dbname]." VALUES(";
					$this->str.="'".implode("' , '", $row)."'";
					$this->str.= ");\n";

				}

			}
		}
		
		$backup_name =  $filename !== NULL ? $filename :  'DB-backup-'.$this->dbname.'-'.date('Y-m-d h-i-s-a');

		//check if the BACK_UP_DB folder exists or else we will create it
		if(file_exists(ROOT_DIR.'BACK_UP_DB')==1){

			$handle = fopen(ROOT_DIR.'BACK_UP_DB/'.$backup_name.'.sql','w+');
			fwrite($handle,$this->str);
			fclose($handle);

		
		}

		else{

			mkdir(ROOT_DIR.'BACK_UP_DB');
			//make sure that folder is writable especially in linux based servers
			chmod(ROOT_DIR.'BACK_UP_DB', 0777);
			$handle = fopen(ROOT_DIR.'BACK_UP_DB/'.$backup_name.'.sql','w+');
			fwrite($handle,$this->str);
			fclose($handle);
			//change permission back to 755
			chmod(ROOT_DIR.'BACK_UP_DB', 0755);
		}

		return $backup_name;
	}//end generateBackUp()

}//end class

?>

