<?
	include('./import.class.php');
	$import = new Import(DOCROOT . 'files/');  
	$import->doReindex = false;              
	//$import->updateImportData(); 
	$imagecheck = $import->picturecheck();

	
	$allSizes = $import->imgsizes();
	$products = $import->loadProducts();  
	
	$completeProducts = array_filter($imagecheck['products'], function($item) {
		foreach($item as $color) {
			foreach($color as $key =>$size) {
				if($size < 1) return false;
			}
		}
		
		return true;
	}); 
	
	$completeOptions = $imagecheck['products'];
	foreach($completeOptions as $sku => $product) {
		foreach($product as $cId => $color) {
			$ok = true;
			foreach($color as $size) {
				if($size < 1) {
					$ok = false;
				}
			}
			if(!$ok) unset($completeOptions[$sku][$cId]);
		}
	 	if(!count($completeOptions[$sku])) unset($completeOptions[$sku]);
	}
	
	$incompleteProducts = $imagecheck['products']; 
	foreach($completeProducts as $sku => $produkt) {
		unset($incompleteProducts[$sku]);
	}
header('Content-Type:	text/html; charset=UTF-8');
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
	<h1>Überprüfung der Bilder</h1>
  
	<h2>Test der Bilder zu den Artikeln</h2>
	
	<table width="100%">
		<tr>
			<th colspan="<?=2+count($allSizes) ?>">Komplette Produkte (<?=count($completeProducts) ?>)</th>
		</tr>
		<? if(count($completeProducts)) : ?>
			<? foreach($completeProducts as $sku => $product) : ?>
				<tr>
					<td width="1"><strong><?=$sku ?></td></strong>
					<td><strong><?=$products[$sku]->name ?></strong></td>
					<? foreach($allSizes as $size) : ?>
					<td width="1"><?=$size ?></td>
					<? endforeach; ?>
				</tr>
				<? foreach($product as $color => $sizes) : ?>
				<tr> 
					<td>&nbsp;</td>
					<td><?=$color ?></td>
					<? foreach($allSizes as $size) : ?>
					<td><?=$sizes[$size] ?></td>
					<? endforeach; ?>
				</tr>
				<? endforeach; ?>
			<? endforeach; ?>
		<? else : ?>
		<tr>
			<td colspan="<?=2+count($allSizes) ?>">Keine Kompletten Produkte gefunden</td>
		</tr>
		<? endif; ?> 
  
	</table>
	
	<table width="100%">
		<tr>
			<th colspan="<?=2+count($allSizes) ?>">Komplette Farboptionen (<?=count($completeOptions) ?>)</th>
		</tr>
		<? if(count($completeOptions)) : ?>
			<? foreach($completeOptions as $sku => $product) : ?>
				<tr>
					<td width="1"><strong><?=$sku ?></td></strong>
					<td><strong><?=$products[$sku]->name ?></strong></td>
					<? foreach($allSizes as $size) : ?>
					<td width="1"><?=$size ?></td>
					<? endforeach; ?>
				</tr>
				<? foreach($product as $color => $sizes) : ?>
				<tr> 
					<td>&nbsp;</td>
					<td><?=$color ?></td>
					<? foreach($allSizes as $size) : ?>
					<td><?=$sizes[$size] ?></td>
					<? endforeach; ?>
				</tr>
				<? endforeach; ?>
			<? endforeach; ?>
		<? else : ?>
		<tr>
			<td colspan="<?=2+count($allSizes) ?>">Keine kompletten Komplette Farboptionen</td>
		</tr>
		<? endif; ?>
  
	</table>
	
	<table width="100%">
		<tr>
			<th colspan="<?=2+count($allSizes) ?>">Produkte mit fehlenden Bildern (<?=count($incompleteProducts) ?>)</th>
		</tr>
		<? if(count($incompleteProducts)) : ?>
			<? foreach($incompleteProducts as $sku => $product) : ?>
				<tr>
					<td width="1"><strong><?=$sku ?></td></strong>
					<td><strong><?=$products[$sku]->name ?></strong></td>
					<? foreach($allSizes as $size) : ?>
					<td width="1"><?=$size ?></td>
					<? endforeach; ?>
				</tr>
				<? foreach($product as $color => $sizes) : ?>
				<tr> 
					<td>&nbsp;</td>
					<td class="<?=(isset($completeOptions[$sku][$color]) ? 'green' : 'red') ?>"><?=$color ?></td>
					<? foreach($allSizes as $size) : ?>
					<td<? if($sizes[$size] == 0) : ?> class="red"<? endif; ?>><?=$sizes[$size] ?></td>
					<? endforeach; ?>
				</tr>
				<? endforeach; ?>
			<? endforeach; ?>
		<? else : ?>
		<tr>
			<td colspan="<?=2+count($allSizes) ?>">Keine Produkte mit fehlenden Bildern gefunden</td>
		</tr>
		<? endif; ?>
  
	</table> 
	
	<h2>Test der einzelnen Bilder</h2>  
	
	<table width="100%">
		<tr>
			<th colspan="6">Richtig zugeordnete Bilder (<?=count($imagecheck['images_ok']) ?>)</th>
		</tr>
		<tr>
			<td><strong>Datei</strong></td>
			<td><strong>SKU</strong></td>
			<td><strong>Farbe</strong></td>
			<td><strong>Name</strong></td>
			<td><strong>Größe</strong></td>
			<td><strong>Nummer</strong></td>
		</tr>
		<? foreach($imagecheck['images_ok'] as $base => $image) : ?>
		<tr>
			<td><?=$base ?></td>
			<td><?=$image->sku ?></td>
			<td><?=$image->color ?></td>
			<td><?=$image->name ?></td>
			<td><?=$image->size ?></td>
			<td><?=$image->num ?></td>
		</tr>	
		<? endforeach; ?>
	</table>   
	
	<table width="100%">
		<tr>
			<th>Bilder im falschen Format (<?=count($imagecheck['errors']['wrong_format']) ?>)</th>
		</tr>
		<? foreach($imagecheck['errors']['wrong_format'] as $image) : ?>
		<tr>
			<td class="red"><?=$image ?></td>
		</tr>	
		<? endforeach; ?>
	</table> 
	
	<table width="100%">
		<tr>
			<th colspan="6">Bilder mit Zuordnungen zu nicht vorhandenen Produkten (<?=count($imagecheck['errors']['no_product']) ?>)</th>
		</tr>
		<tr>
			<td><strong>Datei</strong></td>
			<td><strong>SKU</strong></td>
			<td><strong>Farbe</strong></td>
			<td><strong>Name</strong></td>
			<td><strong>Größe</strong></td>
			<td><strong>Nummer</strong></td>
		</tr>
		<? foreach($imagecheck['errors']['no_product'] as $base => $image) : ?>
		<tr>
			<td><?=$base ?></td>
			<td><?=$image->sku ?></td>
			<td><?=$image->color ?></td>
			<td><?=$image->name ?></td>
			<td><?=$image->size ?></td>
			<td><?=$image->num ?></td>
		</tr>	
		<? endforeach; ?>
	</table>  
	
	<table width="100%">
		<tr>
			<th colspan="6">Bilder mit Zuordnungen zu nicht vorhandenen Produkt-Farben (<?=count($imagecheck['errors']['no_option']) ?>)</th>
		</tr>
		<tr>
			<td><strong>Datei</strong></td>
			<td><strong>SKU</strong></td>
			<td><strong>Farbe</strong></td>
			<td><strong>Name</strong></td>
			<td><strong>Größe</strong></td>
			<td><strong>Nummer</strong></td>
		</tr>
		<? foreach($imagecheck['errors']['no_option'] as $base => $image) : ?>
		<tr>
			<td><?=$base ?></td>
			<td><?=$image->sku ?></td>
			<td><?=$image->color ?></td>
			<td><?=$image->name ?></td>
			<td><?=$image->size ?></td>
			<td><?=$image->num ?></td>
		</tr>	
		<? endforeach; ?>
	</table>
	
	<table width="100%">
		<tr>
			<th colspan="6">Bilder mit falschem Produktnamen (<?=count($imagecheck['errors']['wrong_name']) ?>)</th>
		</tr>
		<tr>
			<td><strong>Datei</strong></td>
			<td><strong>SKU</strong></td>
			<td><strong>Farbe</strong></td>
			<td><strong>Name</strong></td>
			<td><strong>Größe</strong></td>
			<td><strong>Nummer</strong></td>
		</tr>
		<? foreach($imagecheck['errors']['wrong_name'] as $base => $image) : ?>
		<tr>
			<td class="red"><?=$base ?></td>
			<td><?=$image->sku ?></td>
			<td><?=$image->color ?></td>
			<td><?=$image->name ?></td>
			<td><?=$image->size ?></td>
			<td><?=$image->num ?></td>
		</tr>
		<? if(isset($products[$image->sku]) AND $product = $products[$image->sku]) : ?>
		<tr> 
			<td colspan="6" class="green">
				<?="{$image->sku}_{$image->color}_" . $import->slug($product->name) . "_{$image->num}_web_{$image->size}.jpg" ?>
			</td>
		</tr> 
		<? endif; ?>
		<? endforeach; ?>
	</table>
</body>
</html>