<?php
$debug_on = $_GET['debug_on'];
/*
if (isset($_SERVER['REMOTE_ADDR'])) {
	$allowed_ip = array(
		"195.72.178.194",
		"87.106.144.84",
		"217.33.25.59"
	) ;

	if (!in_array($_SERVER['REMOTE_ADDR'],$allowed_ip) ) {
		echo "Invalid IP address : $_SERVER[REMOTE_ADDR]" ;
		exit ;
	}
}*/
ini_set('memory_limit', '2048M');
//set_time_limit ( 12000 ) ;
$now = time() ;
$timestamp = date ("Ymd.Hi", $now) ;
$filename = "feedmanager.$timestamp.csv" ;

if ($_GET['key'] != base64_encode("Gillissa")) {
	echo "Invalid!";
	exit ;
}

if (!$debug_on) {
	header("Content-Type: text/csv; name=$filename;charset=UTF-8;encoding=UTF-8");
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Content-Disposition: attachment; filename=$filename");
} else {
	echo "DEBUG mode";
	echo "<pre>";
}
// Set the operating directory
chdir ($_SERVER[DOCUMENT_ROOT]) ;
require_once './app/Mage.php';
Mage::app();
/*Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_FRONTEND,Mage_Core_Model_App_Area::PART_EVENTS);*/
$_taxHelper  = Mage::helper('tax');
$products = Mage::getModel('catalog/product')->getCollection();
$products->addAttributeToFilter('status', 1);//enabled
//$products->addAttributeToFilter('visibility', 4);//catalog, search
$visibility = array (
	Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
	Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
	Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
);
$products->addAttributeToFilter('visibility', $visibility);
//$products->addAttributeToFilter('type_id', 'simple'); //<-- Multi and simple products
$products->addAttributeToSelect('*');
$products->load();

$childProducts = Mage::getModel('catalog/product_type_configurable');

$directory = $_SERVER[DOCUMENT_ROOT] . "/feedmanager" ;
$separator = " >> " ;

$now = time() ;
$timestamp = date ("Ymd.Hi", $now) ;
//$filename = "feedmanager.$timestamp.csv" ;

if (!$debug_on) {
	$fp = fopen("php://output", 'w');
} else {
	$fp = fopen("feedmanager/$filename", 'w');
}

//$file = $directory."/$filename";
//$fp = fopen($file, "wb"); 

$header = array (
	"Feed Version",
	"2",
	$config[Feed_Manager][feedmanager_export_disabled_cat],
	$config[Feed_Manager][feedmanager_export_out_of_stock], 
	$config[Feed_Manager][feedmanager_export_price_zero]
) ;
putRow ($fp, $header) ;
$paramTitles = array (
	"Store Id",
	"Directory Separator",
	
) ;
putRow ($fp, $paramTitles) ;

$paramValues = array (
	"fashioneyewear.co.uk",
	trim ($separator)
) ;
putRow ($fp, $paramValues) ;

$columns = array (
	"manufacturer",
	"categoryPath",
	"categoryName",
	"productId",
	"parentId",
	"sku",
	"upc",
	"muzeId",
	"isbn",
	"ean",
	"mpn",
	"distributorId",
	"productName",
	"productShortDesc",
	"productDescription",
	"productPromotionTextShort",
	"productPromotionTextLong",
	"productPromotionCode",
	"keywords",
	"offerType",
	"productColour",
	"productSize",
	"weight",
	"productPrice",
	"productUrl",
	"productImage",
	"imageName",
	"extraImage",
	"stockQuantity",
	"availability",
	"condition",
	"gender",
	"warranty",
	"deliveryPrice",
	"deliveryTimeMin",
	"deliveryTimeMax",
	"additionalImage",
	"cost",
	"productPromotionText",
	"frame_shape",
	"frame_colour",
	"frame_size",
	"frame_type",
) ;
putRow ($fp, $columns) ;

foreach ($columns as $colTitle) {
	$blankRow[$colTitle] = "" ;
}
$n = 0 ;

foreach ($products as $prod) {
	if ($n>100000) {
		echo "Failed more than 100,000 ";
		break ;
	}
	
	

	unset ($product) ;

	$product= Mage::getModel('catalog/product');
	$product->load($prod->entity_id);

	//if (!$product->is_salable) continue;

	//Now build the output array
	

	$cat = $product->getCategoryIds();
	
	foreach ($cat as $i => $v) {
		if (!$catLookup[$v]) {
			unset ($catpath) ;
			unset ($catlevel) ;
			$catClass = Mage::getModel('catalog/category')->load($v);
			foreach (explode("/",$catClass->getPath()) as $cat_id) {
				if (!$catIndex[$cat_id]) {
					$catIndex[$cat_id] = Mage::getModel('catalog/category')->load($cat_id)->getName();
					$catIndex[$cat_id] = trim( strtolower( $catIndex[$cat_id] ));
				}

				$catlevel++ ;
				if ( $catIndex[$cat_id] == "root catalog" ) {
					continue ;
				} elseif ($catIndex[$cat_id] == "cat1" ) {
					$catpath = "NULL_DIRECTORY" ;
					break ;
				} else {
					$catpath .=  ($catpath ? " >> " : "") . $catIndex[$cat_id];
				} 
			}
			$catLookup[$v] = $catpath ; 
		}
		if ($catLookup[$v] != "NULL_DIRECTORY") {
			$catArray[$catLookup[$v]] = $catLookup[$v] ;
		}
	}
	
	
	
	// Now we need some funky logic to remove parent folders if they are referenced in a child folder
	krsort( $catArray, SORT_STRING) ;
	$catSort = array() ;
	foreach ($catArray as $k=>$v) {
		$catPath = explode (" >> ", $v) ;
		$parent = &$catSort ;
		foreach ($catPath as $cp) {
			if (!$parent[$cp]) {
				$parent[$cp] = array() ;
			}
			$parent = &$parent[$cp] ;
		}
		// We are at the end so is this an twig node or still a parent
		if (count($parent)) {
			unset ($catArray[$k]) ;
		}
	}
	
	$fm = $blankRow ;

	$fm[categoryPath] = mb_convert_encoding(implode ("\n", $catArray) ,"ISO-8859-1","UTF-8");
	unset ($catArray) ;
	
	$fm[categoryName] = '';
	$fm[productId] = $product->entity_id ;
	$fm[manufacturer] = $product->getAttributeText('manufacturer'); 
	
	$fm['frame_shape'] = $product->getAttributeText('frame_shape');
	$fm['frame_colour'] = $product->getAttributeText('framecolour');
	$fm['frame_size'] = $product->getAttributeText('frame_size');
	$fm['frame_type'] = $product->getAttributeText('frame_type');
	$fm['gender'] = $product->getAttributeText('frame_gender');
	
	$fm[sku] = $product->sku;
	$fm[upc] = "";
	$fm[muzeId] = "" ;
	$fm[isbn] = "" ;
	//$fm[ean] = $child->getEan() ;
	$fm[mpn] = ""; //$product->model ;
	$fm[distributorId] = "" ;

	$fm[productName] = $product->name;
	
	$fm[productDescription] = mb_convert_encoding((strlen($product->description) > 30) ? $product->description : $product->name,"ISO-8859-1","UTF-8");
	$fm[productPromotionText] = "" ;
	$fm[productPromotionCode] = "" ;
	$fm[offerType] = "" ;
	$fm[productColour] = ucwords( $product->getAttributeText('color') ) ; 
	if (empty($fm[productColour])) {
		$fm[productColour] = $fm['frame_colour'];
	}
	$fm[productSize] = '' ;// ucwords( $product->getAttributeText('size') ) ; 
	$fm[weight] = '';// $product->weight;
/*
	if (!$product->special_price) {
		$fm[productPrice] = $product->finalPrice;
	} else {
		$fm[productPrice] = $product->special_price;
	}
	*/
	$fm[productPrice] = $_taxHelper->getPrice($product, $product->getFinalPrice());
	
/*	
	echo "product price".$product->price;echo "<br />";
	echo "final price: $final";
	exit;*/
	/*
	if (stripos($fm[categoryPath], 'eyeglass') !== false) {
		$fm[productPrice] = $fm[productPrice] *1.1;
	} else {
		$fm[productPrice] = $fm[productPrice] *1.2;
	}
	*/
	
//	$fm[productUrl] = $product->getProductUrl();
	$fm[productUrl] = "http://www.fashioneyewear.co.uk/" . $product->getUrlPath();
	
	$fm[productImage] = $product->getImageUrl();
	
	if (strpos($fm[productImage], 'placeholder') !== false || !$fm[productImage]) {
		$fm[productImage] = $product->getSmallImageUrl();
		if (strpos($fm[productImage], 'placeholder') === false && $fm[productImage]) {
			$fm[productImage] = preg_replace("@/small_image/([\d|x]+)/@","/image/",$fm[productImage]);
			$fm[productImage] = preg_replace("@-(.*)\.+@",".",$fm[productImage]);
		}
	}
		$hide_parent = false;

	if ($hide_parent === false) {
		
	}
	if (strpos($fm[productImage], 'placeholder') !== false || !$fm[productImage]) {
		$fm[productImage] = $product->getThumbnailUrl();
		if (strpos($fm[productImage], 'placeholder') === false && $fm[productImage]) {
			$fm[productImage] = preg_replace("@/thumbnail/([\d|x]+)/@","/image/",$fm[productImage]);
			$fm[productImage] = preg_replace("@-(.*)\.+@",".",$fm[productImage]);
		}
	}
		
	// Check extra images in Detailed Image Module 
	$fm[extraImage] = '';

	
	//$fm[availability] = ($fm[stockQuantity]>0)?'In Stock':'Out Of Stock';
	$fm[availability] = ($product->is_salable)?'In Stock':'Out of Stock';
	$fm[stockQuantity] = ($product->is_salable)?$product->getData('stock_item')->getData('qty'):'0';
	$fm[condition] = "New" ;
	$fm[warranty] = "";

	$fm[deliveryPrice] = "";
	$fm[deliveryTimeMin] = "";
	$fm[deliveryTimeMax] = "";
	
	
	$fm[cost] = $product->cost;

	$is_single = clone_product($fm, $product);
	
	if ($is_single) { #we ignore the parent
		if ($fm[productPrice]<1) continue;
		insert_product($fm);
	}
	
	if (!empty($_GET['limit']) && $n>$_GET['limit']) {
		break;
	}
}




$footer = array (
		"TRL",
		$n
	) ;
	putRow ($fp, $footer) ;

	fclose($fp);



function insert_product($fm) {
	global $fp;
	global $n;
	putRow ($fp, $fm) ;
	$n++ ;
}


function clone_product($fm, $product) {
	$is_single = true;
	$option = array();
	
	#get all option parsed
	foreach ($product->getProductOptionsCollection() as $o) {
		#Option
		//echo "<br/>Found options for $fm[productName] (<a href='$fm[productUrl]' target='_blank'>Show</a>)<br />Options are: ".$o->getData('default_title');
		
		$option_title = $o->getData('default_title');
			
		if (stripos($option_title, 'frame size')!== false) {
			$option_type = 'size';
		} else {
			$option_type = 'add_title';
		}
		$values = $o->getValues();
		foreach ($values as $k=>$v) {
			
			$option_line = array();
			
			#Values for option
			$option_value= $v->getData('default_title');
	/*	
			echo "<pre>";
			echo "SKU: $fm[sku]<br />";
			echo "price: $fm[productPrice]<br />";
			print_r($v->debug());
			echo "</pre>";
		*/	
		
			//$debug = $v->debug();
			#Check the product option out of stock?
			if (stripos($option_value, "**out of") !== false) {
				$option_line['stockQuantity'] = 0;
				$option_line['availability'] = 'out of stock';
			} else {
				$option_line['stockQuantity'] = $fm['stockQuantity'];
				$option_line['availability'] = 'in stock';
			}
			
			$option_line['add_sku'] = $k;
			$option_line['add_title'] = $option_value;
			$option_line['price'] = $v->default_price;
			//$option_line['price'] = $option_line['price'];
			
			//echo "SKU $fm[sku] price: $fm[productPrice]<br />";
			//echo "<br />price on $k is $option_line[price], v: {$v->default_price} {$debug[default_price]}<br />";
			if ($fm['productPrice'] == 0 && $option_line['price'] == 0) {
				continue;
			}
			$option[$option_type][] = $option_line;
		}
		if ($is_single) $is_single = false;
	}
	
	
	/*
	echo "<br />";
	echo "$sku";
	echo "<pre>";
	print_r($option);
	echo "</pre>"; */
	
	#Now we collected all the options, let's make the variants with combination
	
	if (!$is_single) {
		#Create variants by combine all the variants together
		unset($fm_variants);
		$fm_variants = array();
		$fm_mod = array();
		
		
		combine_product($option, $fm_mod, $fm_variants); #$fm_variants
		
		
	
		$fmo = $fm;
		$first_variant = true;
		foreach ($fm_variants as $variant) {
			$fm = $fmo;
			#Override the parent data
			if (!$first_variant) {
				$fm['productId'] .= "_".$variant['add_sku'];
			}
			$first_variant  = false;
			$fm['productName'] .= " - ". $variant['add_title'];
			$fm['stockQuantity'] = $variant['stockQuantity'];
			$fm['availability'] = $variant['availability'];
			$fm['sku'] .= "-".$variant['add_sku'];
			$fm['productPrice'] += $variant['price'];
			$fm['productColour'] = $variant['colour'];
			$fm['productSize'] = $variant['size'];
			$fm['parentId'] = $fmo['sku'];
			#insert new child into stdio
			if ($fm[productPrice]<1) continue;
			insert_product($fm);
		} 
	}	
		
	return $is_single;	
}

function combine_product($option, $fm_mod, &$fm_variants) {
//	global $fm_variants;
	global $fm;
	#Save the original $fm data
	$fm_mod_o = $fm_mod;
	
	#Take the first chunck of options
	$cur_option = array_splice($option, 0, 1);
	
	#Get option type (size, colour)
	$option_type = key($cur_option);
	
	#Run through the current array
	foreach (current($cur_option) as $option_line) {
		
		$fm_mod = $fm_mod_o;
			
		$fm_mod['stockQuantity'] = ($option['stockQuantity']>=$fm_mod['stockQuantity'])?$option_line['stockQuantity']:$fm_mod['stockQuantity'];
		
		$fm_mod['availability'] = ($fm_mod['availability'] == 'out of stock')?'out of stock':$option_line['availability'];
		
		
		$fm_mod['add_title'] .= (($fm_mod['add_title'])?" - ":"").$option_line['add_title']; 
		
		$fm_mod['price'] += $option_line['price'];
		
		#If the  option scope is size or colour, we store them to insert into feed col.
		if ($option_type == 'size' || $option_type == 'colour') {
			$fm_mod[$option_type] = $option_line['add_title']; #Size, colour
		}
		
		if ($option_type == 'size' && strlen($option_line['add_title'])<2) {
			$fm_mod['add_sku'] .= (($fm_mod['add_sku'])?"-":"")."Size".$option_line['add_title']; 
		
		} else if ($option_type == 'colour') {
			$colorNames = explode(" ", trim($option_line['add_title']));
			
			$productColour = '';
			foreach ($colorNames as $colorName) {
				$productColour .= Ucfirst(strtolower($colorName));
			}
			$fm_mod['add_sku'] .= (($fm_mod['add_sku'])?"-":"").$productColour;
		} else {
			$fm_mod['add_sku'] .= (($fm_mod['add_sku'])?"-":"").$option_line['add_sku'];
		}
	
		
		if (count($option)>0) {
		
			#If there is another option, need to go through and stick to this option object.
			combine_product($option, $fm_mod);
		} else {
			#If there is no options, we store the option snake.
			
			$fm_variants[] = $fm_mod;
		}
	}

	return ;
}


function putRow ($fc, $line, $format='csv') {
	foreach ($line as $k=>$v) {
		// Clean up any newlines
    		$v = preg_replace('/<br\\\\s*?\\/??>/i', "\\n", $v);
		$v = str_replace("<br />","\n",$v);
		$v = str_replace("\r\n","\n",$v);
		$v = str_replace("\r","\n",$v);
		$v = preg_replace("/\n(\n)+/", "\n\n", $v);

		// Clean up the rest of the code
		$v = strip_tags ( $v ) ;
		$v = html_entity_decode( $v ) ;
		$v = trim ($v) ;
		$line[$k] = $v ;
	}
	fputcsv ($fc, $line) ;
}


