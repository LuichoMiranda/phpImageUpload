<?php
class imageUpload{
	
	/************************************
	* Upload and resize images   *
	* Luis Portillo - Nov, 2010  *
	* Last update: Oct 13, 2013  *
	*************************************/
	
	private $_Width;
	private $_Height;
	private $_SourcePath;
	private $_SourceFile;
	private $_DestinationPath;
	private $_DestinationFile;
	private $_Quality;
	private $_Canvas;
	private $_Center;
	private $_Align;
	private $_OutputFormat;
	private $_Transparency = false;
	private $_TransparentColor = array(0,0,0);
	private $_BackgorundColor  = array(0,0,0);
	
	
	/*convert a non numeric value to zero*/
	private function toNumber($n){
		return is_numeric($n) ? $n : number_format($n, 0, "", "");
	}

	/*set image width and height*/
	public function setSize($w,$h){
		
		if($this->toNumber($w) == 0 && $this->toNumber($h) == 0){
			trigger_error("Width and height must have a value", E_USER_ERROR);
			return false;
		}else{
			$this->_Width  = $w;
			$this->_Height = $h;
		}
		
	}
	
	/*set up image quality*/
	public function setQuality($q){
		$this->_Quality = $this->toNumber($q);
	}
	
	/*set up source folder*/
	public function setSource($s){
		$this->_SourcePath = $s;
	}
	
	/*set up destination folder*/
	public function setDestinationFolder($t){
		$this->_DestinationPath = $t;
	}
	
	/*return uploaded file name*/
	public function getOutputFileName(){
		return $this->_DestinationFile . "." . strtolower($this->_OutputFormat);
	}
	
	/*set up file format JPG|PNG|GIF*/
	public function setOutputFormat($f){
		$formats = array("JPEG","JPG","PNG","GIF");
		if(!array_search(strtoupper($f), $formats) || strtoupper($f) == "JPEG"){
			$this->_OutputFormat = "JPG";
		}else{
			$this->_OutputFormat = strtoupper($f);
		}
	}
	
	/*set up destination file name*/
	public function setOutputFile($f){
		$this->_DestinationFile = $f;
	}
	
	/*set up transparency for png or gif image formats*/
	function setTransparency($c,$t=true){
		if(!is_array($c)){
			trigger_error("Wrong transparency values", E_USER_ERROR);
			return false;
		}else{
			if(count($c) < 3){
				trigger_error("Wrong transparency color values order. Must be RGB", E_USER_ERROR);
				return false;
			}else{
				$this->_Transparency = true;
				$this->_TransparentColor = $c;
			}
		}
	}
	
	/*set background color for the new image*/
	public function setBackgroundColor($c){
		if(!is_array($this->_BackgorundColor)){
			trigger_error("Wrong background color values", E_USER_ERROR);
			return false;
		}else{
			if(count($c) < 3){
				trigger_error("Wrong background color values order. Must be RGB", E_USER_ERROR);
				return false;
			}else{
				$this->_BackgorundColor = $c;
			}
		}
	}
	
	/*set image alingment into the new canvas*/
	private function Alignment($m){
		
		$m = strtolower($m);
		$a = array(
			"top",
			"top_left",
			"top_middle",
			"top_center",
			"top_right",
			"middle_left",
			"middle",
			"center",
			"middle_right",
			"bottom",
			"bottom_left",
			"bottom_middle",
			"bottom_right"
		);
		
		if(!array_search($m, $a)){
			trigger_error("Wrong alingment values", E_USER_ERROR);
			return false;
		}else{
			$this->_Align = $m;
		}
		
	}
	
	public function setAlignment($a){
		$this->Alignment($a);
	}
	
	/*create image resource from file*/
	private function ImageFromFile($f){
		
		
		$ImageInfo = getimagesize($this->_SourcePath . $this->_SourceFile);
		
		switch($ImageInfo['mime']){
			case "image/jpeg":
				$image = imagecreatefromjpeg($f);
				break;
			case "image/gif":
				$image = imagecreatefromgif($f);
				if($this->_Transparency){
					$transparentColor = imagecolorallocate($image, $this->_TransparentColor[0], $this->_TransparentColor[1], $this->_TransparentColor[2]);
					imagecolortransparent($image,  $transparentColor);
				}
				break;
			case "image/png":
				$image = imagecreatefrompng($f);
				imagealphablending($image, false);
				imagesavealpha($image, true);
				break;
			default:
				trigger_error("{$ImageInfo['mime']} is not a valid image format", E_USER_ERROR);
				return false;
		}
		
		return $image;
		
	}
	
	/*set up the image to resize*/
	public function fileToResize($f){
		$this->_SourceFile = $f;
	}
	
	/*resize the image*/
	public function Resize(){
		
		if(!$this->_SourceFile){
			
			trigger_error("File not found", E_USER_ERROR);
			return false;
			
		}else{
			
			if(!$this->_Canvas){
				/* calculate the new image width and height using the original size */
				list($ImageWidth, $ImageHeight, $ImageType, $ImageAttributes) = getimagesize($this->_SourcePath . $this->_SourceFile);
				
				
				if($this->_Height == 0):
					if($ImageWidth < $this->_Width):
						$this->_Height = $ImageHeight;
					else:
						$scaleheight = $this->_Width / $ImageWidth;
						$this->_Height = ceil($ImageHeight * $scaleheight);
					endif;
				endif;
				$NewCanvas = imagecreatetruecolor($this->_Width, $this->_Height);
				if($this->_Transparency){
					
					$transparency = imagecolorallocatealpha($NewCanvas, $this->_TransparentColor[0], $this->_TransparentColor[1], $this->_TransparentColor[2], 127);
					imagefill($NewCanvas, 0, 0, $transparency);
					imagealphablending($NewCanvas, false);
					imagesavealpha($NewCanvas, true);
					
				}
			}else{
				$NewCanvas = $this->ImageFromFile($this->_Canvas);
			}
			
			/*set the background color*/
			if(!$this->_Transparency){
				if($this->_BackgorundColor[0] != 0 && $this->_BackgorundColor[1] != 1 && $this->_BackgorundColor[2] != 0){
					$BgColor = imagecolorallocate($NewCanvas, $this->_BackgorundColor[0], $this->_BackgorundColor[1], $this->_BackgorundColor[2]);
					imagefilledrectangle($NewCanvas, 0, 0, $this->_Width, $this->_Height, $BgColor);
				}
			}
			
			$Image = $this->ImageFromFile($this->_SourcePath . $this->_SourceFile);
			
			
			$Scale = ($ImageWidth < $ImageHeight) ? ($this->_Height / $ImageHeight) : ($this->_Width / $ImageWidth);
			
			$NewWidth	= ceil($ImageWidth * $Scale);
			$NewHeight	= ceil($ImageHeight * $Scale);
			
			/* recalculate with and height if larger*/
			if($NewWidth > $this->_Width):
				$Scale = ($this->_Width / $ImageWidth);
				$NewWidth	= ceil($ImageWidth * $Scale);
				$NewHeight	= ceil($ImageHeight * $Scale);
			endif;
			
			if($NewHeight > $this->_Height):
				$Scale = ($this->_Height / $ImageHeight);
				$NewWidth	= ceil($ImageWidth * $Scale);
				$NewHeight	= ceil($ImageHeight * $Scale);
			endif;
			
			/*calculate X and Y coordinates to align the image*/
			switch($this->_Align){
				case "top":
				case "top_left":
					$destX = 0;
					$destY = 0;
					break;
				case "top_center":
				case "top_middle":
					$destX = ceil(($this->_Width / 2) - ($NewWidth / 2));
					$destY = 0;
					break;
				case "top_right":
					$destX = ceil($this->_Width - $NewWidth);
					$destY = 0;
					break;
				case "center_left":
				case "middle_left":
					$destX = 0;
					$destY = ceil(($this->_Height / 2) - ($NewHeight / 2));
					break;
				case "middle":
				case "center":
					$destX = ceil(($this->_Width / 2) - ($NewWidth / 2));
					$destY = ceil(($this->_Height / 2) - ($NewHeight / 2));
					break;
				case "center_right":
				case "middle_right":
					$destX = ceil($this->_Height - $NewWidth);
					$destY = ceil(($this->_Height / 2) - ($NewHeight / 2));
					break;
				case "bottom":
				case "bottom_left":
					$destX = 0;
					$destY = $this->_Height - $NewHeight;
					break;
				case "bottom_center":
				case "bottom_middle":
					$destX = ceil(($this->_Width / 2) - ($NewWidth / 2));
					$destY = $this->_Height - $NewHeight;
					break;
				case "bottom_right":
					$destX = ceil($this->_Width - $NewWidth);
					$destY = $this->_Height - $NewHeight;
					break;
				default:
					$destX = 0;
					$destY = 0;
					
			}
			
			imagecopyresampled($NewCanvas, $Image, $destX, $destY, 0, 0, $NewWidth, $NewHeight, $ImageWidth, $ImageHeight);
			
			$this->_DestinationFile	= !$this->_DestinationFile	?	$this->_SourceFile : $this->_DestinationFile;
			$this->_Quality		= !$this->_Quality	?	80 : $this->_Quality;
			
			switch($this->_OutputFormat){
				case "JPG":
				case "JPEG":
					imagejpeg($NewCanvas, $this->_DestinationPath . $this->_DestinationFile . "." . strtolower($this->_OutputFormat), $this->_Quality);
					break;
				case "PNG":
					$calidad = ceil($this->_Quality	/ 10);
					imagepng($NewCanvas, $this->_DestinationPath . $this->_DestinationFile . "." . strtolower($this->_OutputFormat), $calidad);
					break;
				case "GIF":
					imagegif($NewCanvas, $this->_DestinationFile . $this->_DestinationFile . "." . strtolower($this->_OutputFormat));
					break;
			}
			
			imagedestroy($NewCanvas);
			imagedestroy($Image);
			
		}
		
	}

}
?>