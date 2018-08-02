<?php
use Aws\S3\S3Client;

class Media extends Generic{
	protected $path, $file, $name, $size, $type, $crop, $cropHeight, $cropWidth, $allowed;

    public function __construct() {

    }

	public function setFile($data = array()) {
		
		if (isset($data['file']) && !empty($data['file'])) {
	        $this->file = $this->secure($data['file']);
	    }

	    if (isset($data['name']) && !empty($data['name'])) {
	        $this->name = $this->secure($data['name']);
	    }

	    if (isset($data['size']) && !empty($data['size'])) {
	        $this->size = $this->secure($data['size']);
	    }

	    if (isset($data['type']) && !empty($data['type'])) {
	        $this->type = $this->secure($data['type']);
	    }

	    if (isset($data['allowed']) && !empty($data['allowed'])) {
	        $this->allowed = $this->secure($data['allowed']);
	    }

	    if (isset($data['crop']) && !empty($data['crop'])) {
	        $this->crop = $data['crop'];
	    }

	    if (isset($data['crop']['height']) && !empty($data['crop']['height'])) {
	        $this->cropHeight = $this->secure($data['crop']['height']);
	    }

	    if (isset($data['crop']['width']) && !empty($data['crop']['width'])) {
	        $this->cropWidth = $this->secure($data['crop']['width']);
	    }
	}

	// Compress image size
	public function compressImage($source_url, $destination_url, $quality) {
	    $info = getimagesize($source_url);
	    if ($info['mime'] == 'image/jpeg') {
	        $image = @imagecreatefromjpeg($source_url);
	        @imagejpeg($image, $destination_url, $quality);
	    } elseif ($info['mime'] == 'image/gif') {
	        $image = @imagecreatefromgif($source_url);
	        @imagegif($image, $destination_url, $quality);
	    } elseif ($info['mime'] == 'image/png') {
	        $image = @imagecreatefrompng($source_url);
	        @imagepng($image, $destination_url);
	    }
	}

	// Crop image + decrease image quality
	public function cropImage($max_width, $max_height, $source_file, $dst_dir, $quality = 80) {
	    $imgsize = @getimagesize($source_file);
	    $width   = $imgsize[0];
	    $height  = $imgsize[1];
	    $mime    = $imgsize['mime'];
	    switch ($mime) {
	        case 'image/gif':
	            $image_create = "imagecreatefromgif";
	            $image        = "imagegif";
	            break;
	        case 'image/png':
	            $image_create = "imagecreatefrompng";
	            $image        = "imagepng";
	            break;
	        case 'image/jpeg':
	            $image_create = "imagecreatefromjpeg";
	            $image        = "imagejpeg";
	            break;
	        default:
	            return false;
	            break;
	    }
	    $dst_img    = @imagecreatetruecolor($max_width, $max_height);
	    $src_img    = $image_create($source_file);
	    $width_new  = $height * $max_width / $max_height;
	    $height_new = $width * $max_height / $max_width;
	    if ($width_new > $width) {
	        $h_point = (($height - $height_new) / 2);
	        @imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
	    } else {
	        $w_point = (($width - $width_new) / 2);
	        @imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
	    }
	    @imagejpeg($dst_img, $dst_dir, $quality);
	    if ($dst_img)
	        @imagedestroy($dst_img);
	    if ($src_img)
	        @imagedestroy($src_img);
	}

	// upload files to sever
	public function uploadFile($type = 0, $delete_from_stroage = true) {
		if (!file_exists('media/upload/photos/' . date('Y'))) {
			@mkdir('media/upload/photos/' . date('Y'), 0777, true);
		}
		if (!file_exists('media/upload/photos/' . date('Y') . '/' . date('m'))) {
			@mkdir('media/upload/photos/' . date('Y') . '/' . date('m'), 0777, true);
		}

		if (!file_exists('media/upload/videos/' . date('Y'))) {
			@mkdir('media/upload/videos/' . date('Y'), 0777, true);
		}
		if (!file_exists('media/upload/videos/' . date('Y') . '/' . date('m'))) {
			@mkdir('media/upload/videos/' . date('Y') . '/' . date('m'), 0777, true);
		}

		if (!file_exists('media/upload/files/' . date('Y'))) {
			@mkdir('media/upload/files/' . date('Y'), 0777, true);
		}
		if (!file_exists('media/upload/files/' . date('Y') . '/' . date('m'))) {
			@mkdir('media/upload/files/' . date('Y') . '/' . date('m'), 0777, true);
		}

		$new_string        = pathinfo($this->name, PATHINFO_FILENAME) . '.' . strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
		$extension_allowed = explode(',', $this->allowed);
		$file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);

		if (!in_array($file_extension, $extension_allowed)) {
			return array(
				'error' => 'File format not supported'
			);
		}

		if ($file_extension == 'jpg' || $file_extension == 'jpeg' || $file_extension == 'png' || $file_extension == 'gif') {
			$folder   = 'photos';
			$fileType = 'image';
		} else if ($file_extension == 'mp4' || $file_extension == 'webm' || $file_extension == 'flv') {
			$folder   = 'videos';
			$fileType = 'video';
		} else {
			$folder   = 'files';
			$fileType = 'file';
		}

		if (empty($folder) || empty($fileType)) {
			return false;
		}

		$ar = array(
			'video/mp4',
			'video/mov',
			'video/mpeg',
			'video/flv',
			'video/avi',
			'video/webm',
			'audio/wav',
			'audio/mpeg',
			'video/quicktime',
			'audio/mp3',
			'image/png',
			'image/jpeg',
			'image/gif'
		);

		if (!in_array($this->type, $ar)) {
			return array(
				'error' => 'File format not supported'
			);
		}

		$dir         = "media/upload";
		$file_path   = "{$folder}/" . date('Y') . '/' . date('m') . '/' . $this->generateKey(50,50) . '_' . date('d') . '_' . md5(time()) . "_{$fileType}.{$file_extension}";
		$filename    = $dir . '/' . $file_path;
		$second_file = pathinfo($filename, PATHINFO_EXTENSION);
		if (move_uploaded_file($this->file, $filename)) {
			if ($second_file == 'jpg' || $second_file == 'jpeg' || $second_file == 'png' || $second_file == 'gif') {
				if ($second_file != 'gif') {
					if (!empty($this->crop)) {
						$crop_image = $this->cropImage($this->cropWidth, $this->cropHeight, $filename, $filename, 100);
					}
					$this->compressImage($filename, $filename, 90);
				}
			}

			$last_data             = array();
			$last_data['filename'] = $filename;
			$last_data['name']     = $this->name;
			if (self::$config['ftp_upload'] == 1 && $delete_from_stroage == true) {
				$upload_     = $this->uploadToFtp($filename, $delete_from_stroage);
			} else if (self::$config['amazone_s3'] == 1 && $delete_from_stroage == true) {
				$upload_     = $this->uploadToS3($filename, $delete_from_stroage);
			}
			return $last_data;
		}
	}

	public function uploadToFtp($filename = '', $delete_file = true) {
		if (empty(self::$config['ftp_host']) || empty(self::$config['ftp_username']) || empty(self::$config['ftp_password']) || empty(self::$config['ftp_port'])) {
            return false;
		}

		$ftp = new \FtpClient\FtpClient();
        $ftp->connect(self::$config['ftp_host'], false, self::$config['ftp_port']);
        $login = $ftp->login(self::$config['ftp_username'], self::$config['ftp_password']);

		if ($login) {
            $file_path = substr($filename, 0, strrpos( $filename, '/'));
            $file_path_info = explode('/', $file_path);
            $path = '';
            if (!$ftp->isDir($file_path)) {
                foreach ($file_path_info as $key => $value) {
                    $path .= '/' . $value . '/' ;
                    if (!$ftp->isDir($path)) {
                        $mkdir = $ftp->mkdir($path);
                    }
                } 
            }
            $ftp->chdir($file_path);
            if ($ftp->putFromPath($filename)) {
            	if ($delete_file == true) {
                	@unlink($filename);
            	}
                return true;
            }
		}
		
	}
	
	public function uploadToS3($filename = '', $delete_file = true) {
		if (empty(self::$config['amazone_s3_key']) || empty(self::$config['amazone_s3_s_key']) || empty(self::$config['region']) || empty(self::$config['bucket_name'])) {
            return false;
		}

		$s3 = new S3Client([
            'version'     => 'latest',
            'region'      => self::$config['region'],
            'credentials' => [
                'key'    => self::$config['amazone_s3_key'],
                'secret' => self::$config['amazone_s3_s_key'],
            ]
        ]);

        $s3->putObject([
            'Bucket' => self::$config['bucket_name'],
            'Key'    => $filename,
            'Body'   => fopen($filename, 'r+'),
            'ACL'    => 'public-read',
            'CacheControl' => 'max-age=3153600',
		]);
		
		if ($s3->doesObjectExist(self::$config['bucket_name'], $filename)) {
			if ($delete_file == true) {
				@unlink($filename);
			}
            return true;
        }
	}

	public function deleteFromFTPorS3($filename) {

	    if (self::$config['amazone_s3'] == 0 && self::$config['ftp_upload'] == 0) {
	        return false;
	    }

	    if (self::$config['ftp_upload'] == 1) {
	        $ftp = new \FtpClient\FtpClient();
	        $ftp->connect(self::$config['ftp_host'], false, self::$config['ftp_port']);
        	$login = $ftp->login(self::$config['ftp_username'], self::$config['ftp_password']);
	        
	        if ($login) {
	            $file_path = substr($filename, 0, strrpos( $filename, '/'));
	            $file_name = substr($filename, strrpos( $filename, '/') + 1);
	            $file_path_info = explode('/', $file_path);
	            $path = '';
	            if (!$ftp->isDir($file_path)) {
	                return false;
	            }
	            $ftp->chdir($file_path);
	            $ftp->pasv(true);
	            if ($ftp->remove($file_name)) {
	                return true;
	            }
	        }
	    } else {
	        $s3Config = (
	            empty(self::$config['amazone_s3_key']) || 
	            empty(self::$config['amazone_s3_s_key']) || 
	            empty(self::$config['region']) || 
	            empty(self::$config['bucket_name'])
	        ); 

	        if ($s3Config){
	            return false;
	        }
	        $s3 = new S3Client([
	            'version'     => 'latest',
	            'region'      => self::$config['region'],
	            'credentials' => [
	                'key'    => self::$config['amazone_s3_key'],
	                'secret' => self::$config['amazone_s3_s_key'],
	            ]
	        ]);

	        $s3->deleteObject([
	            'Bucket' => self::$config['bucket_name'],
	            'Key'    => $filename,
	        ]);

	        if (!$s3->doesObjectExist(self::$config['bucket_name'], $filename)) {
	            return true;
	        }
	    }
	    return true;
	}

	public function isImage($file_path = ''){
		if (file_exists($file_path)) {
			$image      = getimagesize($file_path);
			$mime_types = array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_BMP);

	        if (in_array($image[2], $mime_types)) {
	            return true;
	        }
		}

		return false;
	}

	public function ImportImage($url = '', $type = 0) {
		$this->initDir();
		$dir         = "media/upload";
		$file_path   = "photos/" . date('Y') . '/' . date('m') . '/' . $this->generateKey(50,50) . '_' . date('d') . '_' . md5(time()) . "_image.jpg";
		$filename    = $dir . '/' . $file_path;
		$put_file = file_put_contents($filename, $this->curlConnect($url));
		if ($type == 1) {
			$crop_image = $this->cropImage(150, 150, $filename, $filename, 100);
		}
		if (file_exists($filename)) {
			$this->uploadToS3($filename);
			$this->uploadToFtp($filename);
		}
		return $filename;
	}

	public function initDir($dir = 'photos'){
		if (!file_exists("media/upload/$dir/" . date('Y'))) {
            @mkdir("media/upload/$dir/" . date('Y'), 0777, true);
        }

	    if (!file_exists("media/upload/$dir/" . date('Y') . '/' . date('m'))) {
	        @mkdir("media/upload/$dir/" . date('Y') . '/' . date('m'), 0777, true);
	    }
		
		return $this;
	}
}
?>