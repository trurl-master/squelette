<?php

namespace Squelette;

// define('RESIZE_BY_HEIGHT', 1);
// define('RESIZE_BY_WIDTH', 2);
// define('RESIZE_BY_MAJOR', 3);
// define('RESIZE_BY_WIDTH_HEIGHT', 4);
// define('RESIZE_COVER', 4);
// define('RESIZE_CONTAIN', 5);


class Image {

    const
        RESIZE_BY_HEIGHT = 1,
        RESIZE_BY_WIDTH = 2,
        RESIZE_BY_MAJOR = 3,
        RESIZE_BY_WIDTH_HEIGHT = 4,
        RESIZE_COVER = 4,
        RESIZE_CONTAIN = 5;

    const JPEG_QUALITY = 90;

    public static function open($name)
    {

        $exp = explode(".", $name);
        $ext = strtolower(array_pop($exp));

        if ($ext === 'jpg') {
            $ext = 'jpeg';
        }

        $fn_name = 'imagecreatefrom'.$ext;

        if (function_exists($fn_name)) {
            $img = call_user_func($fn_name, $name);
            if (!$img) {
                throw new Exception('error creating thumb');
                // die('{"success": false, "message": "error creating thumb"}');
            }
        } else {
            throw new Exception('unsupported image format: ' . $ext);
            // die('{"success": false, "message": "unsupported image format: ' . $ext . '"');
        }

        return array($img, $ext);
    }

    public static function save($dst_img, $ext, $filename)
    {
        $result = true;

        switch($ext) {
            case 'jpg':
            case 'jpeg':
                imageinterlace($dst_img, 1); // enables progressive jpeg
                $result = imagejpeg($dst_img, $filename, self::JPEG_QUALITY);

                break;
            case 'png':
                // integer representation of the color black (rgb: 0,0,0)
                // $background = imagecolorallocate($dst_img, 0, 0, 0);
                // removing the black from the placeholder
                // imagecolortransparent($dst_img, $background);

                imagealphablending($dst_img, true);
                imagesavealpha($dst_img, true);
                $result = imagepng($dst_img, $filename, 9);
                break;
            case 'gif': $result = imagegif($dst_img, $filename);
                break;
            default: throw new Exception('unsupported image format: ' . $ext);
        }

        if (!$result) {
            throw new Exception('unable to save resized image: ' . $filename);
            // die('{"success": false, "message": "unable to save resized image: ' . $filename . '"}');
        }

        return $result;
    }


    public static function resize($from_filepath, $to_filepath, &$new_w, &$new_h, $by = self::RESIZE_BY_HEIGHT)
    {

        list($src_img, $ext) = self::open($from_filepath);

        // fix orientation
        if ($ext === 'jpeg') {
            $exif = @exif_read_data($from_filepath);

            if(!empty($exif['Orientation'])) {
                switch($exif['Orientation']) {
                    case 3: // upside down
                        $src_img = imagerotate($src_img, 180, 0);
                        break;
                    case 6: // rotated 90° right
                        $src_img = imagerotate($src_img, -90, 0);
                        break;
                    case 8: // rotated 90° left
                        $src_img = imagerotate($src_img, 90, 0);
                        break;
                }
            }
        }

        //
        $width = imageSX($src_img);
        $height = imageSY($src_img);

        //
        if ($width !== $new_w || $height !== $new_h) {

            $aspect = $width / $height;
            $src_x = $src_y = 0;

            if ($by === self::RESIZE_BY_MAJOR) {
                $by = $width > $height ? self::RESIZE_BY_WIDTH : self::RESIZE_BY_HEIGHT;
            }

            switch($by) {
                default:
                case self::RESIZE_BY_HEIGHT:
                    $thumb_h = $new_h;
                    $thumb_w = $new_h * $aspect;
                    break;
                case self::RESIZE_BY_WIDTH:
                    $thumb_w = $new_w;
                    $thumb_h = $new_w / $aspect;
                    break;
                case self::RESIZE_BY_WIDTH_HEIGHT:
                    $thumb_w = $new_w;
                    $thumb_h = $new_h;

                    // calculate crop x,y
                    $new_aspect = $new_w / $new_h;

                    if ($new_aspect < $aspect) { // if thumb is narrower than original image -> crop width
                        $src_x = ($width - $height*$new_aspect)/2;
                        $width = $height*$new_aspect;
                        $src_y = 0;
                    } else { // thumb is wider than original
                        $src_x = 0;
                        $src_y = ($height - $width/$new_aspect)/2;
                        $height = $width/$new_aspect;
                    }

                    break;
            }

            $dst_img = imagecreatetruecolor($thumb_w, $thumb_h);

            if ($ext === 'png' || $ext === 'gif') {
                imagealphablending( $dst_img, false );
                imagesavealpha( $dst_img, true );
                // $black = imagecolorallocate($dst_img, 0, 0, 0);
                // imagecolortransparent($dst_img, $black);
            }

            imagecopyresampled($dst_img, $src_img, 0, 0, $src_x, $src_y, $thumb_w, $thumb_h, $width, $height);

            $result = self::save($dst_img, $ext, $to_filepath);

            imagedestroy($dst_img);

        } else {
            $result = self::save($src_img, $ext, $to_filepath);
            $thumb_w = $width;
            $thumb_h = $height;
        }

        imagedestroy($src_img);

        $new_w = $thumb_w;
        $new_h = $thumb_h;

        return $result;
    }
}
