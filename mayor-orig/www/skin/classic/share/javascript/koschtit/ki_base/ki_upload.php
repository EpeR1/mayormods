<?php
session_start();

include_once("../ki_config/ki_setup.php");

$pwok = 0;
if(isset($_SESSION['pwquery'])){
	if($_SESSION['pwquery'] === $pw || $_SESSION['pwquery'] === $userpw)$pwok = 1;
}

if($pwok == 1){
	$supported = array("jpg","png","gif", "jpeg");
	
	if(is_uploaded_file($_FILES['pic']['tmp_name'])){
	
		$thefile = $_FILES['pic'];
	
		if(isset($_POST['watermark'])){
			$target_name = "../ki_base/ki_watermark.pic";
			$temp = explode('.', strtolower($thefile['name']));
			if(in_array($temp[1], $supported)){
				if(move_uploaded_file($thefile['tmp_name'], $target_name)){
?>
<script>
parent.kiu.uploadedWatermark();
</script>
<?php
				}
			}
		} else {
			
			if(!isset($_POST['dir']))
				exit();
			else	
				$dir = $_POST['dir'];

			if(!isset($_POST['addwatermark']))
				exit();
			else	
				$addwatermak = intval($_POST['addwatermark']);
			
			// -------------- Sicherheitsabfragen!
			if(preg_match("/[\.]*\//", $dir))exit();
			// ---------- Ende Sicherheitsabfragen!

			if(is_file("../ki_config/".$dir."_ki_setup.php"))include_once("../ki_config/".$dir."_ki_setup.php");
			
			$target_path = "../ki_galleries/".$dir."/";
			$temp = explode('.', strtolower($thefile['name']));
			if(in_array($temp[1], $supported)){
				if($temp[1] === "jpeg")$temp[1] = "jpg";
				$target_name = $target_path.$temp[0].".".$temp[1]; // Endgültige Pfad+Name
				if(move_uploaded_file($thefile['tmp_name'], $target_name)){

					// Load the image where the logo will be embeded into
					switch($temp[1]){
						case "jpg":
							$image = imagecreatefromjpeg($target_name);
						break;
						case "png":
							$image = imagecreatefrompng($target_name);
						break;
						case "gif":
							$image = imagecreatefromgif($target_name);
						break;
					}
					imagealphablending($image, true);
					imagesavealpha($image, true);
					
					// Get dimensions
					$imageWidth = imagesx($image);
					$imageHeight = imagesy($image);
					$imageWidth_o = $imageWidth;
					$imageHeight_o = $imageHeight;

					if($addwatermak == 1){
						if(is_file("../ki_base/ki_watermark.pic")){
							// Load the logo image
							$logoImage = imagecreatefrompng("../ki_base/ki_watermark.pic");
							if(!$logoImage){
								$logoImage = imagecreatefromjpeg("../ki_base/ki_watermark.pic");
							}
							if(!$logoImage){
								$logoImage = imagecreatefromgif("../ki_base/ki_watermark.pic");
							}
							if(!$logoImage){
								exit();
							}							
							imagealphablending($logoImage, true);
							imagesavealpha($logoImage, true);

							$logoWidth = imagesx($logoImage);
							$logoHeight = imagesy($logoImage);
							$logoWidth_o = $logoWidth;
							$logoHeight_o = $logoHeight;
							
							if($watermark_size > 0){
								$logoAspect = $logoWidth / $logoHeight;

								if($imageWidth > $imageHeight){
									$wide = 1;	
								} else {
									$wide = 0;	
								}
								
								if($logoWidth > $logoHeight){
									$logoWide = 1;	
								} else {
									$logoWide = 0;	
								}
								
								if($wide == 1){
									if($logoWide == 1){
										$logoWidth = round($watermark_size * $imageWidth);
										$logoHeight = round((1/$logoAspect) * $logoWidth);
										if($logoHeight > $imageHeight){
											$logoHeight = round($watermark_size * $imageHeight);
											$logoWidth = round($logoAspect * $logoHeight);	
										}
									} else {
										$logoHeight = round($watermark_size * $imageHeight);
										$logoWidth = round($logoAspect * $logoHeight);
									}
								} else {
									if($logoWide == 0){
										$logoHeight = round($watermark_size * $imageHeight);
										$logoWidth = round($logoAspect * $logoHeight);
										if($logoWidth > $imageWidth){
											$logoWidth = round($watermark_size * $imageWidth);
											$logoHeight = round((1/$logoAspect) * $logoWidth);
										} else {
											$logoWidth = round($watermark_size * $imageWidth);
											$logoHeight = round((1/$logoAspect) * $logoWidth);
										}
									}
								}
							}
								
							switch($watermark_vert){
								case "top":
									$starty = 0;
								break;
								case "middle";
									$starty = round(($imageHeight - $logoHeight)*0.5);
								break;
								case "bottom":
									$starty = $imageHeight-$logoHeight;
								break;	 
							}
							switch($watermark_hori){
								case "left":
									$startx = 0;
								break;
								case "center";
									$startx = round(($imageWidth - $logoWidth)*0.5);
								break;
								case "right":
									$startx = $imageWidth-$logoWidth;
								break;	 
							}
							
							// Paste the logo
							imagecopyresampled($image, $logoImage, $startx, $starty, 0, 0, $logoWidth, $logoHeight, $logoWidth_o, $logoHeight_o);

							imageDestroy($logoImage);
						}
					}
					
					$maxx = 10000;
					$maxy = 10000;
					if(isset($_POST['maxx'])){
						if(intval($_POST['maxx']) > 0)$maxx = intval($_POST['maxx']);
					}
					if(isset($_POST['maxy'])){
						if(intval($_POST['maxy']) > 0)$maxy = intval($_POST['maxy']);
					}

					$aspect = $imageWidth / $imageHeight;
					
					if($aspect > 1){
						if($imageWidth > $maxx){
							$imageWidth = $maxx;
							$imageHeight = round((1/$aspect) * $imageWidth);
						}
						if($imageHeight > $maxy){
							$imageHeight = $maxy;
							$imageWidth = round($aspect * $imageHeight);
						}
					} else {
						if($imageHeight > $maxy){
							$imageHeight = $maxy;
							$imageWidth = round($aspect * $imageHeight);
						}
						if($imageWidth > $maxx){
							$imageWidth = $maxx;
							$imageHeight = round((1/$aspect) * $imageWidth);
						}
					}
					
					if($imageWidth_o != $imageWidth || $imageHeight_o != $imageHeight){
						$bild = imagecreatetruecolor($imageWidth, $imageHeight);
						imagealphablending($bild, false);
						imagesavealpha($bild, true);
						imagecopyresampled($bild, $image, 0, 0, 0, 0, $imageWidth, $imageHeight, $imageWidth_o, $imageHeight_o); 
						switch($temp[1]){
							case "jpg":
								imagejpeg($bild, $target_name, 80);
							break;
							case "png":
								imagepng($bild, $target_name);
							break;
							case "gif":
								imagegif($bild, $target_name);
							break;
						}
						imageDestroy($bild);
					} else {
						if($addwatermak == 1){
							// Save image
							switch($temp[1]){
								case "jpg":
									imagejpeg($image, $target_name, 80);
								break;
								case "png":
									imagepng($image, $target_name);
								break;
								case "gif":
									imagegif($image, $target_name);
								break;
							}
						}
					}
					
					// Release memory
					imageDestroy($image);
					
					echo "done";
				}
			}
		}
	}
}
?>