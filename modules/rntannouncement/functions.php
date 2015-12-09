<?php

/**
 * Закачка файлов
 *
 * @param array $files
 * @param string $from
 * @return array
*/
function downloadFiles($files, $count, $name, $dir, $resample_size = array(), $quality = 85, $delete = false) {
    $images = $photo_arr = array();
    $change1 = array('а', 'б', 'в',	'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
    $change2 = array('a', 'b', 'v',	'g', 'd', 'e', 'jo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shh', '', 'y', '', 'je', 'ju', 'ja', '');
    for ($i=0; $i<$count; $i++) {
        if (is_array($files['fld']['name'][$name])) {
            if (!empty($files['fld']['name'][$name][$i])) {
                $filename = $files['fld']['name'][$name][$i];
                $filename = str_replace($change1, $change2, strtolower($filename));
                $full_filename = $dir.'/'.$filename;
                if ($resample_size) {
                    // Проверяем, изображение это или нет
                    $size = getimagesize($files['fld']['tmp_name'][$name][$i]);
                    if (!$size) continue;
                }
                if (@move_uploaded_file($files['fld']['tmp_name'][$name][$i], $full_filename) !== false) {
                    @chmod($full_filename, 0664);
                    if ($resample_size) {
						$del = $delete;                    	
                    	foreach ($resample_size as $key=>$val) {
							$image = ResampleImage($dir, $filename, $val, false, $quality);
							if ($image) {
								$image = path($image);
								$photo_arr[$i][$key] = $image;
								ksort($photo_arr[$i]);
							}
                    	}
                    	// Проверяем перед удалением оригинала, сжималось изображение или нет, 
                    	// если нет то не удаляем оригинал
						if ($del && $photo_arr[$i]['image_large']!=$full_filename) unlink ($full_filename);
                    }
                    else $photo_arr[$i] = $filename;
                }
            }
        } else {
            if (!empty($files['fld']['name'][$name])) {
                $filename = $files['fld']['name'][$name];
                $filename = str_replace($change1, $change2, strtolower($filename));
                $full_filename = $dir.'/'.$filename;
                if ($resample_size) {
                    // Проверяем, изображение это или нет
                    $size = getimagesize($files['fld']['tmp_name'][$name]);
                    if (!$size) continue;
                }
                if (@move_uploaded_file($files['fld']['tmp_name'][$name], $full_filename) !== false) {
                    @chmod($full_filename, 0664);
                    if ($resample_size) {
                        // Ресайз изображения
                        $file['image_large'] = $full_filename;
                        $file['image_small'] = ResampleImage($dir, $filename, $resample_size, $delete, $quality);
                        $images[] = $file;
                    }
                    else $images[] = $filename;
                    $photo_arr = $images;
                }
            }
        }
    }
    return $photo_arr;
}

# Ресайз изображения
function ResampleImage($file_dir, $file_name, $size, $delete = false, $quality) {
	$dot   = strrpos($file_name, '.');
	$fext  = substr($file_name, $dot);
	switch (strtolower($fext)) {
		case '.jpeg': ;                                           // расширение не поддерживается:
		case '.jpg': {if (!function_exists('ImageCreateFromJPEG')) { return false;} break;}
		case '.gif': {if (!function_exists('ImageCreateFromGIF')) { return false;} break;}
		case '.png': {if (!function_exists('ImageCreateFromPNG')) { return false;} break;}
		default: return false;
	}

	$img_size = @GetImageSize($file_dir.'/'.$file_name);
	if ($img_size[2]>3){ return false; } # 1-GIF;2-JPG;3-PNG
	if (isset($size[1])) {
		$new_size = ImageSize2($img_size, $size[0], $size[1]);
	}
	else {
		$new_size = ImageSize2($img_size, $size[0]);
	}

	if ($img_size[0] == $new_size[0] && $img_size[1] == $new_size[1]){
		$ret = $file_dir.'/'.$file_name;
		return imgpath($ret); # No changes needed
	}

	switch ($img_size[2]) {
		case 1: $src = @ImageCreateFromGIF($file_dir.'/'.$file_name);  break;
		case 2: $src = @ImageCreateFromJPEG($file_dir.'/'.$file_name);  break;
		case 3: $src = @ImageCreateFromPNG($file_dir.'/'.$file_name);  break;
	}
	if (!$src)  return $file_name;

	if ($img_size[2]!='1' && function_exists('ImageCreateTrueColor')) {
		 # GIF не поддерживается
		 $dst = ImageCreateTrueColor($new_size[0], $new_size[1]);
		 ImageCopyResampled($dst, $src, 0, 0, 0, 0, $new_size[0], $new_size[1], $img_size[0], $img_size[1]);
	} else {
		 $dst = ImageCreateTrueColor($new_size[0], $new_size[1]);
		 $trans_color = imagecolorallocate($dst, 255, 0, 0);
		 $color = imagecolorallocate($dst, 255, 255, 255);
		 imagecolortransparent($dst, $trans_color);
		 ImageCopyResized($dst, $src, 0, 0, 0, 0, $new_size[0], $new_size[1], $img_size[0], $img_size[1]);	
 	}
	if (!$delete) {
		switch ($img_size[2]) {
			case 1: ImageGIF($dst, $file_dir.'/'.$file_name.'1'); break;
			case 2: ImageJPEG($dst, $file_dir.'/'.$file_name.'1', $quality); break;
			case 3: ImagePNG($dst, $file_dir.'/'.$file_name.'1'); break;
		}
	}
	else {
		switch ($img_size[2]) {
			case 1: ImageGIF($dst, $file_dir.'/'.$file_name); break;
			case 2: ImageJPEG($dst, $file_dir.'/'.$file_name, $quality); break;
			case 3: ImagePNG($dst, $file_dir.'/'.$file_name); break;
		}
	}
	ImageDestroy($src);
	ImageDestroy($dst);

	$new_name = new_file_name($file_name,"_".$size[0]);
	if (file_exists($file_dir.'/'.$new_name)) {
		$num = 1;
		while (file_exists($file_dir.'/'.new_file_name($file_name, '['.$num.']'."_".$size[0]))) {
			$num++;
		}
		$new_name = new_file_name($file_name, '['.$num.']'."_".$size[0]);
	}
	if (!$delete) {
		rename($file_dir.'/'.$file_name.'1', $file_dir.'/'.$new_name);
	}
	else {
		rename($file_dir.'/'.$file_name, $file_dir.'/'.$new_name);
	}

	$ret = $file_dir.'/'.$new_name;
	return imgpath($ret);
}

function downloadFiles2($files, $idarr, $name, $fieldname,  $dir, $choose, $resample_size = array(), $quality = 85, $delete = false) {
    $images = $photo_arr = array();
    $change1 = array('а', 'б', 'в',	'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
    $change2 = array('a', 'b', 'v',	'g', 'd', 'e', 'jo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shh', '', 'y', '', 'je', 'ju', 'ja', '');
	foreach ($idarr AS $i=>$v) {
        if (is_array($files['fld']['name'][$name][$fieldname])) {
            if (!empty($files['fld']['name'][$name][$fieldname][$i]) && $choose[$i]) {
                $filename = $files['fld']['name'][$name][$fieldname][$i];
                $filename = str_replace($change1, $change2, strtolower($filename));
                $full_filename = $dir.'/'.$filename;
                if ($resample_size) {
                    // Проверяем, изображение это или нет
                    $size = getimagesize($files['fld']['tmp_name'][$name][$fieldname][$i]);
                    if (!$size) continue;
                }
                if (@move_uploaded_file($files['fld']['tmp_name'][$name][$fieldname][$i], $full_filename) !== false) {
                    @chmod($full_filename, 0664);
                    if ($resample_size) {
						$del = $delete;                    	
                    	foreach ($resample_size as $key=>$val) {
							$image = ResampleImage($dir, $filename, $val, false, $quality);
							if ($image) {
								$image = path($image);
								$photo_arr[$i][$key] = $image;
								ksort($photo_arr[$i]);
							}
                    	}
                    	// Проверяем перед удалением оригинала, сжималось изображение или нет, 
                    	// если нет то не удаляем оригинал
						if ($del && $photo_arr[$i]['image_large']!=$full_filename) unlink ($full_filename);
                    }
                    else $photo_arr[$i] = $filename;
                }
            }
        } else {
            if (!empty($files['fld']['name'][$name][$fieldname])) {
                $filename = $files['fld']['name'][$name][$fieldname];
                $filename = str_replace($change1, $change2, strtolower($filename));
                $full_filename = $dir.'/'.$filename;
                if ($resample_size) {
                    // Проверяем, изображение это или нет
                    $size = getimagesize($files['fld']['tmp_name'][$name][$fieldname]);
                    if (!$size) continue;
                }
                if (@move_uploaded_file($files['fld']['tmp_name'][$name][$fieldname], $full_filename) !== false) {
                    @chmod($full_filename, 0664);
                    if ($resample_size) {
                        // Ресайз изображения
                        $file['image_large'] = $full_filename;
                        $file['image_small'] = ResampleImage($dir, $filename, $resample_size, $delete, $quality);
                        $images[] = $file;
                    }
                    else $images[] = $filename;
                }
            }
        }
    }
    return $photo_arr;
}

function new_file_name($file_name, $suffix) {
	$dot = strrpos($file_name, '.');
	$fbase = substr($file_name, 0, $dot);
	$fext = strtolower(substr($file_name, $dot));
	return $fbase.$suffix.$fext;
}

function ImageSize2($img_size, $max_width=0, $max_height=0) {
	if ($max_width && $img_size[0]>$max_width) {
		$img_size[1]=round($max_width*$img_size[1]/$img_size[0]); $img_size[0]=$max_width;
	}
	if ($max_height && $img_size[1]>$max_height) {
		$img_size[0]=round($max_height*$img_size[0]/$img_size[1]); $img_size[1]=$max_height;
	}
	return $img_size;
}

/*
	Функция номарлизует внешний вид пути
	к виду "bla/bla/bla"
*/
function imgpath(&$path) {	
	$path = str_replace('\\', '/', $path);		
	$elems = explode("/", $path);
	foreach ($elems as $k=>$v){
		if (empty($v) && !is_numeric($v)) unset($elems[$k]);
	}
	$path = (strpos($path, '/') === 0 ? "/" : "");
	$path .= implode("/", $elems);
	return $path;
}

?>
