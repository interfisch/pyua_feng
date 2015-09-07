<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_generate_cat_id.php													*
*  inkludiert von dmc_write_art.php 										*
*  Kategorie ID basierend auf Metas mappen									*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/
 
		if (GENERATE_CAT_ID) {
			if (DEBUGGER>=50) fwrite($dateihandle, "dmc_generate_cat_id - KatID alt = ".$Kategorie_ID." -> ");	
			// Zugehörige Kategorie über WaWi Kategorie ID aus Keyword Eintrag der Katgorien ermitteln:
			if($Kategorie_ID<>CAT_ROOT) {
				// Ueberpruefen auf multiple Kategorien  <Artikel_Kategorie_ID>210802,610704,710902</Artikel_Kategorie_ID>
				if (strpos($Kategorie_ID, CAT_DEVIDER) === false) {
					$Kategorie_IDs[0] = dmc_get_cat_keywords($Kategorie_ID);
					if($Artikel_Typ=='configurable')
						$Kategorie_IDs[1] = CAT_ROOT;
				} else {
					$Kategorie_IDs = explode(CAT_DEVIDER,$Kategorie_ID);
					for ( $i = 0; $i < count ( $Kategorie_IDs ); $i++ )
					{			
						// Magento KategorieIds ermitteln
						$Kategorie_IDs[$i] = dmc_get_cat_keywords(trim($Kategorie_IDs[$i]));
					} // end for	
					if($Artikel_Typ=='configurable')
							$Kategorie_IDs[count($Kategorie_IDs)] = CAT_ROOT;
				}
			  }
		} else {	
			//***     MultiCatgegorien getrennt durch ein Komma (  ,  )  bzw CAT_DEVIDER   ***\\
			$Kategorie_IDs = explode(CAT_DEVIDER,$Kategorie_ID);
			if($Artikel_Typ=='configurable') {
				$Kategorie_IDs[count($Kategorie_IDs)]= CAT_ROOT;
			}
		}//end else

		// Wenn Vater Kategorie nicht vorhanden, nehme Root Kategorie
		if ($Kategorie_IDs[0] == -1) $Kategorie_IDs[0] = CAT_ROOT;		// 3 = Root Categorie
		
		if (DEBUGGER>=50) fwrite($dateihandle, ", neu -> ".$Kategorie_IDs[0]." und ".$Kategorie_IDs[1]." \n");	
			
?>
	