<?php
include('../conf/definitions/de.inc.php');
echo'<html><head><link rel=stylesheet type="text/css" href="style.css"></head><body>';
	
echo "<h3>".$DMC_TEXT['GETPWD']."</h3>";	
echo "<p>".$DMC_TEXT['GETPWD_DESC']."</p>";	
echo	'<table>'.
										'<form name="pwd" action="'.$_SERVER['PHP_SELF'].'" method="post">'.
										//'<form action="'.$_SERVER['PHP_SELF'].'" method="post" id="'.$Key.'">'.
										'<input type="hidden" name="action" value="getpwd">'.
										'<tr>'.
											'<td>Password aus CAO:</td>'.
											'<td><input name="password" type="password" id="password" size="20" value="" /></td>'.
										'</tr>';
					if ($_POST['action']=='getpwd') {
									echo	'<tr>'.
												'<td>Password f&uuml;r Magento Web-User:</td>'.
												'<td>'.md5($_POST['password']).'</td>'.
											'</tr>';
					}
					echo					'<tr>'.
											'<td>&nbsp;</td>'.
											'<td><input type="submit" name="submit" value="'.$DMC_TEXT['GETPWD_BTN'].'"></td>'.
										'</tr>';
	echo '</table></body></html>';
	
echo '<br/><center><a href="JavaScript:window.close()">Close</a></center>';

echo "</body></html>";
?>