<?

include('./import.class.php');    

class Import2 extends Import {
	public function __destruct() {
		return;
	}
}

$import = new Import2(DOCROOT . 'files/');  
$import->doReindex = false;    

Mage::app()->setCurrentStore(1);

if(!$_GET || !isset($_GET['sku'])) {
  echo "Keine sku!";
	exit; 
}                      

$sku = $_GET['sku'];

if(!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku)) {
	echo "Artikel nicht gefunden";
	exit;
} 

$sizes = array(
	'listing',
	'produkt',
	'thumb',
	'warenkorb',
	'zoom',
);

?>
<!DOCTYPE html>                  
<html>
<head>  
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Test</title>
	<style>
 		body {
			font-size:13px;
			font-family:Arial;
		}
		table {
			border-collapse:collapse;
			margin-bottom:20px;
		}
		table td, table th {
			text-align:left;
			border:1px solid #fff;
			padding:3px;
			background:#eee;
		}
		
		table th {
			padding:5px 3px;
			background:#ccc;
		} 
		
		table td.red {
			color:red;
		}
		
		table td.green {
			color:green;
		}
	</style>
</head>
<body>
	<h1>Überprüfung der Bilder für <?=$product->name ?> (<?=$product->sku ?>)</h1>
	<? foreach($product->getColors() as $color): ?>
	<h2>Farbe <em><?=$color->name ?></em> (<?=$color->id ?> / qty: <?=$color->qty ?>) <span style="border:1px solid black;display:inline-block;width:40px;height:20px;background:<?=$color->rgb1 ?>"><span style="margin-left:20px;display:inline-block;width:20px;height:20px;background:<?=$color->rgb2 ? $color->rgb2 : $color->rgb1 ?>"></span></span></h2>
	<table>
		<tr>
			<th>Größe</th>
			<th>Anzahl</th>
		</tr>
		<? foreach($sizes as $size) : ?> 
		<? $imgs = $product->getProductImages($size, $color->code); ?>
		<tr>
			<td><?=$size ?></td>
			<td><?=count($imgs) ?></td>
		</td>
		<tr>
			<td colspan="2">
				<? var_dump($imgs); ?>
			</td>
		</tr>
		<? endforeach; ?>
	</table>
	<? endforeach; ?>
</body>
</html>