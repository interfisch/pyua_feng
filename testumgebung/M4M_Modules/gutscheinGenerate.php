<?php
/*
	Description:	Diese Funktion generiert einen Gutscheincode nach speziellen Parametern
	Date:			07.10.2014
	Author:			Flemming von Seht
*/
#error_reporting(E_ALL);
#ini_set('display_errors', 1);



class GutscheinGenerate {
	protected $RID;
	protected $COD;
	protected $MAIL;
	protected $ULI;
	protected $UPC;
	protected $FCOD;
	protected $LEN 		 = 10;
	protected $DB 	     = "testumgebung";
	protected $DBPRAEFIX = "pyua_onlineshop_2649_";
	protected $DBTABLE 	 = "salesrule_coupon";
	protected $DBFIELDS	 = "`coupon_id`, `rule_id`, `code`, `usage_limit`, `usage_per_customer`, `times_used`, `expiration_date`, `created_at`, `type`";
	/*protected $DBFIELDS	 = "`coupon_id`, `rule_id`, `code`, `usage_limit`, `usage_per_customer`, `times_used`, `expiration_date`, `is_primary`, `created_at`, `type`";*/
	/*
	Description: diese Funktion ist die einzige, welche aufgerufen wird.
	0. FUC = Funktion			-> 	0 = generiere Code & sende Mail
									1 = generiere Code
	1. RID = rule_id 			-> beinhaltet die ID der übergeordneten Regel
	2. COD = CODE				-> beinhaltet einen Parameter, welcher 
								   den Code einzigartig macht
								   !!! Wenn FUC = 2 -> kommen hier die Codes hinein
    3. MAIL= EMail d. Kunden	-> an diese Adresse wird nachher der Code gesendet
	4. ULI = usage_limit 		-> beinhaltet die Anzahl der Benutzung
	5. UPC = usage_per_customer	-> beinhaltet den wert wie oft der EINE Benutzer diesen 
								   Gutschein nutzen darf
	*/
	public function start($FUC=0, $RID, $COD, $MAIL, $ULI=1, $UPC=1){
		$this -> RID = $RID;
		$this -> COD = $COD;
		$this -> MAIL= $MAIL;
		$this -> ULI = $ULI;
		$this -> UPC = $UPC;

		// generiere Full Code
		$this -> FCOD= $this -> generateFullCode();

		// Datenbank schreiben
		$this -> writeDB($this -> generateSaveString());

		if($FUC == 0){
			// send Mail
			$this -> sendMail($this->generateMail($this -> FCOD));
		}
		return $this -> FCOD;
	}

	private function generateFullCode(){
		// Key generieren
		$Base1	=	date("YmdBHis", time());
		$Base2	=	substr(date("u", time()),1,2);
		// Base zusammensetzen 
		$Base 	=	md5($Base1.$Base2);
		// Laenge berechnen
		$LenCOD	=	count($this -> COD);
		$LenBase=	$this -> LEN - $LenCOD;
		// Base kuerzen
		$Base 	=	substr($Base, 0, $LenBase);
		$Base 	=   strtoupper($this -> COD.$Base);
		#echo $Base;
		// Key wiedergeben
		return $Base;
	}

	/*
	Description: Hier wird der String geschrieben, welcher nachher in die 
				 DB geschrieben wird.				 
	*/
	private function generateSaveString(){
		$Values = "'',".
				  "'".$this -> RID."',".
				  "'".$this -> FCOD."',".
				  "'".$this -> ULI."',".
				  "'".$this -> UPC."',".
				  "'0',".
				  "'',".
				  "'".date("Y-m-d H:i:s", time())."',".
				  "'1'";

		// Savestring generieren
		return "INSERT INTO `".$this -> DBPRAEFIX.$this -> DBTABLE."`(".$this -> DBFIELDS.") VALUES (".$Values.")";
	}

	private function writeDB($SAVE){
		#echo $SAVE;
		// DB Connect
		$mysqli = new mysqli("localhost", "root", "eeW1Roo?th", $this -> DB);
		// in Datenbank schreiben
		$mysqli->query($SAVE);	
		// DB Close
		$mysqli->close();
	}

	private function generateMail($KeyText){
		// Content der mail generieren
		$Content	 =	"Dies sind ihre Gutscheine: ".$KeyText;
		return $Content;
	}

	public function setSendMail($Email, $Content){
		// Emailadresse schreiben
		$this -> MAIL = $Email;
		$this -> sendMail($Content);
	}
	
	private function sendMail($Content){
		// Mail an $MAIL versenden
		$to 		= $this -> MAIL;
	   	$subject 	= "Ihr persönlicher Gutschein";
	   	$message	= $Content;
	   	$header 	= "From:PYUA Ecorrect Outerwear <welcome@pyua.de> \n";
	   	$header    .= "MIME-Version: 1.0\n";
	   	$header    .= "Content-type: text/html\n";
	   	$retval 	= mail($to,$subject,$message,$header);
	}
}


?>