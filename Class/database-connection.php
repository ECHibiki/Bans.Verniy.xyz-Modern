<?php	
	class DatabaseConnection{
		private $sql_data = array();
		private $connection = null;
		
		public $die_state = array();
		public $comment_error = false;
		public $delete_status = false;
		
		private $path_prefix="";
		
		public $alphabet = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p","q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
		
		function __construct($path_prefix = ""){
			$this->path_prefix = $path_prefix;
			$sql_ini = fopen($path_prefix . "Settings/sql.ini", "r");
			if(!$sql_ini) $sql_ini = fopen($path_prefix . "settings/sql.ini", "r");
			while(!feof($sql_ini)){
				$line = fgets($sql_ini);
				$key = substr($line, 0, strpos($line, "="));
				$value = trim(substr($line, strpos($line, "=")+1));
				$this->sql_data[$key] = $value;
			}
			$this->connectToDatabase();
		}
		
		function connectToDatabase(){	
			try {
				$this->connection = new PDO ("mysql:dbname=" . $this->sql_data["database"] . ";host=" . $this->sql_data["connection"],
													$this->sql_data["user"], 	$this->sql_data["pass"]);
				$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			} catch (PDOException $e) {
				$this->logsys .= "Failed to get DB handle: " . $e->getMessage() . "\n";
			}
		}
		
		
		function addToTable($tablename, $paramaters){
			$param_len = sizeof($paramaters);
			$bind_string = "";
			$table_string= "";
			$first_comma = false;
			foreach($paramaters as $key => $param){
				if(!$first_comma){
				$bind_string = ":$key";
				$table_string = "`$key`";
				$first_comma = true;
				}
				else{
					$bind_string .= ",:$key";
					$table_string .= ",`$key`";
				} 
			}
			$statement = $this->connection->prepare("INSERT INTO `".$this->sql_data["database"] ."`.`$tablename`($table_string) VALUES(" . $bind_string . ")");
		
			$index = 0;
			foreach($paramaters as $key => $param){
				$success =	$statement->bindParam(":" . $key , $paramaters[$key]);
				$index++;
			}
			try{
				$statement->execute();
			}catch(Exception  $e){
			   echo "<strong>" . $e->getMessage() . "</strong><br/>";
			}	
		}
		
		function massAddToTable($tablename, $all_paramaters){
			$value_string = "";
			$table_string= "";
			$update_string = "";
			$first_comma = false;
			$first_value_comma = false;
			$table_string_created = false;
			foreach($all_paramaters as $index=>$paramaters){
				if(!$first_value_comma){
					$value_string = "(";
				}
				else{
					$value_string .= ",(";
				}
				foreach($paramaters as $key => $param){
					if(!$first_comma){
						$value_string .= ":$key$index";		
						if(!$table_string_created){
							$update_string = "$key=VALUES($key)";
							$table_string = "`$key`";
						} 
						$first_comma = true;
					}
					else{
						$value_string .= ",:$key$index";
						if(!$table_string_created){
							$update_string .= ",$key=VALUES($key)";
							$table_string .= ",`$key`";
						} 
					} 
				}
				$value_string .= ")";
				$first_comma = false;
				$first_value_comma = true;
				$table_string_created = true;
			}
						
			$statement = $this->connection->prepare("INSERT INTO `".$this->sql_data["database"] ."`.`$tablename`($table_string) VALUES $value_string
														ON DUPLICATE KEY UPDATE $update_string");
			foreach($all_paramaters as $index=>$paramaters){
				foreach($paramaters as $key => $param){
					$success =$statement->bindParam(":$key$index" , $paramaters[$key]);
				}
			}		
			try{
				$statement->execute();
			}catch(Exception  $e){
				echo "<strong>" . $e->getMessage() . "</strong><br/>";
			}			
		}
			
		function getPostDetails($table, $refining_paramater="", $bind_val=""){
			$statement = NULL;
			if($refining_paramater == "" || $bind_val == ""){
				$statement = $this->connection->prepare("SELECT * FROM `$table`");
			}
			else{
				$statement = $this->connection->prepare("SELECT * FROM `$table` WHERE `$table`.`$refining_paramater` = :bindval");
				$statement->bindParam(":bindval", $bind_val);
			}
			try{
				$response = $statement->execute();
				return $statement->fetchAll();
			}catch(Exception  $e){
			   echo "<strong>" . $e->getMessage() . "</strong><br/>";
			}	
		}
		
		function getPostDetailsAllUpperLower($table, $boundry_param, $lower_limit){
			$statement = $this->connection->prepare("SELECT * FROM `$table` WHERE $boundry_param > :lower_limit AND $boundry_param <= :upper_limit ORDER BY $boundry_param ASC");
			$statement->bindParam(":lower_limit", $lower_limit);
			$upper_limit = $lower_limit + 1000;
			$statement->bindParam(":upper_limit", $upper_limit);
			try{
				$response = $statement->execute();
				return $statement->fetchAll();
			}catch(Exception  $e){
			   echo "<strong>" . $e->getMessage() . "</strong><br/>";
			}	
		}
		
		function getPostDetailsLimit($table, $boundry_param, $lower_limit, $added_search, $added_search_value){
			$statement = $this->connection->prepare("SELECT * FROM `$table` WHERE $added_search = :added_search_value  ORDER BY $boundry_param ASC LIMIT :lower_limit, 1000");
			$statement->bindParam(":added_search_value", $added_search_value);

			$statement->bindParam(":lower_limit", $lower_limit , PDO::PARAM_INT);
			try{
				$response = $statement->execute();
				return $statement->fetchAll();
			}catch(Exception  $e){
			   echo "<strong>" . $e->getMessage() . "</strong><br/>";
			}	
		}
		function getPostDetailsAllSettingsLimit($table, $boundry_param, $lower_limit, $board, $com, $reason){
			$statement = $this->connection->prepare("SELECT * FROM `$table` WHERE board LIKE :board AND com LIKE :com AND reason LIKE :reason ORDER BY $boundry_param ASC LIMIT :lower_limit, 1000");
			if($board == "") $board = "%";
			else $board = "$board";
			$com = "%$com%";
			$reason = "%$reason%";
			$statement->bindParam(":board", $board);
			$statement->bindParam(":com", $com);
			$statement->bindParam(":reason", $reason);
			$statement->bindParam(":lower_limit", $lower_limit, PDO::PARAM_INT);
			try{
				$response = $statement->execute();
				return $statement->fetchAll();
			}catch(Exception  $e){
			   echo "<strong>" . $e->getMessage() . "</strong><br/>";
			}	
		}
		
		function getCountOf($table, $count_refine, $count_refine_value=null){
			$statement = null;
			if($count_refine_value == null){
				$statement = $this->connection->prepare("SELECT COUNT(*) FROM `$table`");
			}
			else{
				$statement = $this->connection->prepare("SELECT COUNT(*) FROM `$table` WHERE $count_refine LIKE :count_refine_value");
				$statement->bindParam(":count_refine_value", $count_refine_value);
			}
			//var_dump($statement);
			try{
				$response = $statement->execute();
				return $statement->fetchAll();
			}catch(Exception  $e){
			   echo "<strong>" . $e->getMessage() . "</strong><br/>";
			}
		}
		
		function getCountOfAllSettings($table, $board, $com, $reason){
			$statement = $this->connection->prepare("SELECT COUNT(*) FROM `$table` WHERE board LIKE :board AND com LIKE :com AND reason LIKE :reason");
			if($board == "") $board = "%";
			else $board = "$board";
			$com = "%$com%";
			$reason = "%$reason%";
			$statement->bindParam(":board", $board);
			$statement->bindParam(":com", $com);
			$statement->bindParam(":reason", $reason);
			try{
				$response = $statement->execute();
				return $statement->fetchAll();
			}catch(Exception  $e){
			   echo "<strong>" . $e->getMessage() . "</strong><br/>";
			}
		}
		
		function updatePost($table, $table_search, $table_search_value, $keyed_params){
			$first_entry = true;
			$set_string = "";
			foreach($keyed_params as $key=>$value){
				if($first_entry){
					$set_string = "$key=:$key";
				}
				else $set_string .= ",$key=:$key";
			}
			
			$statement = $this->connection->prepare("UPDATE `$table` SET $set_string WHERE $table_search=:param_to_find");		
			$statement->bindParam(":param_to_find", $table_search_value);
			
			foreach($keyed_params as $key=>$value){
				$statement->bindParam(":$key", $value);
			}
			
			try{
				$response = $statement->execute();
			}catch(Exception  $e){
			   echo "<strong>" . $e->getMessage() . "</strong><br/>";
			}	
		}		
	}
?>