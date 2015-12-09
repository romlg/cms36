<?php
	/****************************************************************************************
	*                 Class for creating an image watermark on image                        *
	*              (c)2004 TOTH Richard, riso.toth@seznam.cz, Slovakia                      *
	*                              rev 0.92  (18.12.2004)                                   *
	*****************************************************************************************
	* Simple use:                                                                           *
	*                                                                                       *
	* require "./class.rwatermark.php";                                                     *
	* $handle = new RWatermark(FILE_JPEG, "./original.jpg");                                *
	* $handle->SetPosition("RND");                                                          *
	* $handle->SetTransparentColor(255, 0, 255);                                            *
	* $handle->SetTransparency(60);                                                         *
	* $handle->AddWatermark(FILE_PNG, "./watermark.png");                                   *
	* Header("Content-type: image/png");                                                    *
	* $handle->GetMarkedImage(_IMG_PNG);                                                     *
	* $handle->Destroy();                                                                   *
	****************************************************************************************/
	
	Define("STREAM", 0);
	Define("HANDLE", 1);
	Define("FILE_JPEG", 2);
	Define("FILE_GIF", 3);
	Define("FILE_PNG", 4);
	Define("FILE_WBMP", 5);
	Define("FILE_XBM", 6);
	Define("FILE_XPM", 7);
	Define("__IMG_JPEG", 8);
	Define("__IMG_PNG", 9);
	Define("__IMG_GIF", 10);
	
	
    class RWatermark 
    {
    	var $original_image = -1;
    	var $mark_image = -1;
    	var $marked_image = -1;
    	var $original_width;
    	var $original_height;
    	var $mark_width;
    	var $mark_height;
    	var $mark_position = "RND";
    	var $mark_offset_x = 0;
    	var $mark_offset_y = 0;
    	var $transparent_color = -1; 
    	var $transparency = 100;
    	var $gd_version;
        var  $version = 'RWatermark 0.92';
    
    
    	// Constructor RWatermark
    	// $type = input type (STREAM, FILE_JPEG, FILE_GIF, FILE_PNG, FILE_WBMP, FILE_XBM, FILE_XPM, HANDLE)
    	// $str = input original image (stream, filename or handle)
    	// Return true on valid parameter, otherwise false
    	function RWatermark($type, $str) 
        {
            $this->setGDInfo();
            
            $type = StrToUpper($type);
            switch ($type)
            {
                case STREAM:
                    $this->original_image = ImageCreateFromString($str);
                    break;
                case FILE_JPEG:
                    $this->original_image = ImageCreateFromJPEG($str);
                    break;
                case FILE_GIF:
                    $this->original_image = ImageCreateFromGIF($str);
                    break;
                case FILE_PNG:
                    $this->original_image = ImageCreateFromPNG($str);
                    break;
                case FILE_WBMP:
                    $this->original_image = ImageCreateFromWBMP($str);
                    break;
                case FILE_XBM:
                    $this->original_image = ImageCreateFromXBM($str);
                    break;
                case FILE_XPM:
                    $this->original_image = ImageCreateFromXPM($str);
                    break;
                case HANDLE:
                    $this->mark_image = $str;
                    break;
                default:
                    $this->dieError("Unknown input type in constructor method!");
                    return false;
            }
            
    		if (!$this->original_image)
            {
                $this->dieError("GD command error in constructor method!");
                return false;
            }
    		$this->original_width = ImageSX($this->original_image);
    		$this->original_height = ImageSY($this->original_image);
    		
    		return true;
    	}
    	
    	
    	
    	// Adds a watermark to the image
    	// $type = input type (STREAM, FILE_JPEG, FILE_GIF, FILE_PNG, FILE_WBMP, FILE_XBM, FILE_XPM, HANDLE)
    	// $str = input mark image (stream, filename or handle)
    	// Return true on valid parameter, otherwise false
    	function AddWatermark($type, $str) 
        {
            $type = StrToUpper($type);
            switch ($type)
            {
                case STREAM:
                    $this->mark_image = ImageCreateFromString($str);
                    break;
                case FILE_JPEG:
                    $this->mark_image = ImageCreateFromJPEG($str);
                    break;
                case FILE_GIF:
                    $this->mark_image = ImageCreateFromGIF($str);
                    break;
                case FILE_PNG:
                    $this->mark_image = ImageCreateFromPNG($str);
                    break;
                case FILE_WBMP:
                    $this->mark_image = ImageCreateFromWBMP($str);
                    break;
                case FILE_XBM:
                    $this->mark_image = ImageCreateFromXBM($str);
                    break;
                case FILE_XPM:
                    $this->mark_image = ImageCreateFromXPM($str);
                    break;
                case HANDLE:
                    $this->mark_image = $str;
                    break;
                default:
                    $this->dieError("Unknown input type in addWatermark method!");
                    return false;
            }
            
            if (!$this->mark_image)
            {
                $this->dieError("GD command error in addWatermark method!");
                return false;
            }
            
            // get size of mark
            $this->mark_width = ImageSX($this->mark_image);
    		$this->mark_height = ImageSY($this->mark_image);

            // calculate offsets
            $this->getOffsets();
            
            // merge images
            $this->createMarkedImage($type);
            
            return true;
    	}
    
    
    
    	// Public int getMarkedImage
    	// Returns the final image
    	// $type = type of output (HANDLE, _IMG_PNG, _IMG_JPEG, IMG_WBMP)
    	// $param1 & $param2 $ $param3 = depend on $type
    	// - _IMG_PNG -> $param1 = filename
    	// - _IMG_GIF -> $param1 = filename
    	// - _IMG_JPEG -> $param1 = filename, $param2 = quality, $param3 = interleaced (YES/NO)
    	// Return true on valid parameter, otherwise false
    	function GetMarkedImage($type = HANDLE, $param1 = "", $param2 = "", $param3 = "") 
        {
            if ($this->marked_image != -1)
            {
                $type = StrToUpper($type);
                switch ($type)
                {
                    case HANDLE:
                        return $this->marked_image;
                    case _IMG_PNG:
                        if ($param1 != "") {ImagePNG($this->marked_image, $param1);}
                        else {ImagePNG($this->marked_image);}
                        return true;
                    case _IMG_GIF:
                        if ($param1 != "") {ImageGIF($this->marked_image, $param1);}
                        else {ImageGIF($this->marked_image);}
                        return true;
                    case _IMG_JPEG:
                        if (StrToUpper($param3) == "YES") {ImageInterlace($this->marked_image, 1);} 
                        else {ImageInterlace($this->marked_image, 0);}
                        if ($param2 == "") {$param2 = 75;}
                        ImageJPEG($this->marked_image, $param1, $param2);
                        return true;
                    default:
                        $this->dieError("Unknown output type in getMarkedImage method!");
                }
            }
            return false;
    	}
    
    	// Set position of watermark on image
    	// Return true on valid parameter, otherwise false
    	function SetPosition($newposition = "RND", $x = 0, $y = 0)
        {
    		$valid_positions = array("TL", "TM", "TR", "CL", "CM", "CR", "BL", "BM", "BR", "ABS", "RND");
    
    		$newposition = StrToUpper($newposition);
    
    		if (In_Array($newposition, $valid_positions)) 
            {
                $this->mark_position = $newposition;
    			if ($this->mark_position == "ABS")
    			{
    			    $this->mark_offset_x = $x;
    			    $this->mark_offset_y = $y;
    			}
    			return true;
    		}
    		return false;
    	}
    	
    	// Sets the transparency in %
    	// Return true on valid parameter, otherwise false
    	function SetTransparency($trans)
    	{
            if (($trans >= 0) && ($trans <= 100))
            {
                $this->transparency = 100 - $trans;
                return true;
            }
            return false;
    	}
    	
    	// Sets the color in the mark which would be transparent
    	// Return true on valid parameter, otherwise false
    	function SetTransparentColor($r,$g,$b)
    	{
    	    if (($r >= 0) && ($r <= 255) && ($g >= 0) && ($g <= 255) && ($b >= 0) && ($b <= 255))
    	    {
    	        $this->transparent_color = ($r << 16) + ($g << 8) + $b;
    	        return true;
    	    }
    	    return false;
    	}
    	
    	// Destroys he images in memory
    	function Destroy()
    	{
    	    if ($this->original_image != -1) {ImageDestroy($this->original_image);}
    	    if ($this->marked_image != -1) {ImageDestroy($this->marked_image);}
    	    if ($this->mark_image != -1) {ImageDestroy($this->mark_image);}
    	}
    
        /****************************************** PRIVATE IMPLEMENTATIONS **********************************************/
    
        function createMarkedImage($type)
        {
            // Create marked image (original + watermark)
            $this->marked_image = ImageCreateTrueColor($this->original_width, $this->original_height);
            ImageCopy($this->marked_image, $this->original_image, 0, 0, 0, 0, $this->original_width, $this->original_height);

            if ($this->transparent_color != -1)
            {
                $transparent_color_index = ImageColorExact($this->mark_image, ($this->transparent_color >> 16) & 0xFF, ($this->transparent_color >> 8) & 0xFF, $this->transparent_color & 0xFF);
                ImageColorTransparent($this->mark_image,$transparent_color_index);
            }
            if ($this->gd_version >= 2) // GD2: Should be the easy way
            {
                ImageAlphaBlending($this->marked_image, true);
            }
            if ($type == FILE_PNG) {
                ImageCopy($this->marked_image, $this->mark_image, $this->mark_offset_x, $this->mark_offset_y, 0, 0, $this->mark_width, $this->mark_height);
            }
            else {
                ImageCopyMerge($this->marked_image, $this->mark_image, $this->mark_offset_x, $this->mark_offset_y, 0, 0, $this->mark_width, $this->mark_height, $this->transparency);
            }
        }

    
    	function getOffsets() 
        {
    		$width_left = $this->original_width - $this->mark_width;
    		$height_left = $this->original_height - $this->mark_height; 
    	
            switch ($this->mark_position) 
            {
                case "TL": // Top Left
                    $this->mark_offset_x = $width_left >= 5 ? 5 : $width_left;
    				$this->mark_offset_y = $height_left >= 5 ? 5 : $height_left;
    				break;
    			case "TM": // Top middle 
    				$this->mark_offset_x = intval(($this->original_width - $this->mark_width) / 2);
    				$this->mark_offset_y = $height_left >= 5 ? 5 : $height_left;
    				break;
    			case "TR": // Top right
    				$this->mark_offset_x = $this->original_width - $this->mark_width;
    				$this->mark_offset_y = $height_left >= 5 ? 5 : $height_left;
    				break;
    			case "CL": // Center left
    				$this->mark_offset_x = $width_left >= 5 ? 5 : $width_left;
    				$this->mark_offset_y = intval(($this->original_height - $this->mark_height) / 2);
    				break;
    			case "CM": // Center middle
    				$this->mark_offset_x = intval(($this->original_width - $this->mark_width) / 2);
    				$this->mark_offset_y = intval(($this->original_height - $this->mark_height) / 2);
    				break;
    			case "CR": // Center right
    				$this->mark_offset_x = $this->original_width - $this->mark_width;
    				$this->mark_offset_y = intval(($this->original_height - $this->mark_height) / 2);
    				break;
    			case "BL": // Bottom left
    				$this->mark_offset_x = $width_left >= 5 ? 5 : $width_left;
    				$this->mark_offset_y = $this->original_height - $this->mark_height - 5;
    				break;
    			case "BM": // Bottom middle
    				$this->mark_offset_x = intval(($this->original_width - $this->mark_width) / 2);
    				$this->mark_offset_y = $this->original_height - $this->mark_height - 5;
    				break;
    			case "BR": // Bottom right
    				$this->mark_offset_x = $this->original_width - $this->mark_width - 5;
    				$this->mark_offset_y = $this->original_height - $this->mark_height - 5;
    				break;
    			case "ABS": // Absolute position
    				$this->mark_offset_x = ($this->mark_offset_x >= 0) && ($this->mark_offset_x <= $width_left) ? $this->mark_offset_x : 0;
    				$this->mark_offset_y = ($this->mark_offset_y >= 0) && ($this->mark_offset_y <= $height_left) ? $this->mark_offset_y : 0;
    				break;
    			case "RND":
    			    $this->mark_offset_x = Rand(5, $width_left - 5);
    			    $this->mark_offset_y = Rand(5, $height_left - 5);
    			    break;
    		}
    	}
    	
    	
        function dieError($err)
    	{
            Die($err);
    	}
    	
    	
    	function setGDInfo()
    	{
            $gdinfo = gd_info();
    		if (PReg_Match('/(\d)\.\d/', $gdinfo["GD Version"], $gdinfo)) {$this->gd_version = $gdinfo[1];}
    		else {$this->gd_version = 0;}
    		UnSet($gdinfo);
    	}
    }
?>
