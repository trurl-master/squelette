<?php

namespace Squelette;

class Image {

    const
        RESIZE_BY_HEIGHT = 1,
        RESIZE_BY_WIDTH = 2,
        RESIZE_BY_MAJOR = 3,
        RESIZE_BY_WIDTH_HEIGHT = 4,
        RESIZE_COVER = 4,
        RESIZE_CONTAIN = 3;

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
            }
        } else {
            throw new Exception('unsupported image format: ' . $ext);
        }

        // fix orientation
        if ($ext === 'jpeg') {
            $exif = @exif_read_data($name);

            if(!empty($exif['Orientation'])) {
                switch($exif['Orientation']) {
                    case 3: // upside down
                        $img = imagerotate($img, 180, 0);
                        break;
                    case 6: // rotated 90° right
                        $img = imagerotate($img, -90, 0);
                        break;
                    case 8: // rotated 90° left
                        $img = imagerotate($img, 90, 0);
                        break;
                }
            }
        }

        return [$img, $ext];
    }

    public static function save($image, $ext, $filename)
    {
        $result = true;

        switch($ext) {
            case 'jpg':
            case 'jpeg':
                imageinterlace($image, 1); // enables progressive jpeg
                $result = imagejpeg($image, str_replace('jpeg', 'jpg', $filename), self::JPEG_QUALITY);

                break;
            case 'png':
                // integer representation of the color black (rgb: 0,0,0)
                // $background = imagecolorallocate($dst_img, 0, 0, 0);
                // removing the black from the placeholder
                // imagecolortransparent($dst_img, $background);

                imagealphablending($image, false);
                imagesavealpha($image, true);
                $result = imagepng($image, $filename, 9);
                break;
            case 'gif':
                imagealphablending($image, false);
                imagesavealpha($image, true);
                $result = imagegif($image, $filename);
                break;
            default: throw new \Exception('unsupported image format: ' . $ext);
        }

        if (!$result) {
            throw new \Exception('unable to save resized image: ' . $filename);
        }

        return $result;
    }

    public static function rename($from, $to, $format)
    {

        $info = pathinfo($to);
        $to = $info['dirname'] . '/' . $info['filename'] . '.' . $format;

        if (pathinfo($from, PATHINFO_EXTENSION) === $format) {
            rename($from, $to);
        } else {
            $image = self::open($from);
            self::save($image, $format, $to);

            unlink($from);
        }
        
    }

    public static function resize($image, &$config)
    {

        $width = imageSX($image);
        $height = imageSY($image);
        $aspect = $width / $height;

        if ($config['by'] === self::RESIZE_CONTAIN) {
            $new_aspect = $config['width'] / $config['height'];

            $by = $aspect > $new_aspect ? self::RESIZE_BY_WIDTH : self::RESIZE_BY_HEIGHT;
        } else {
            $by = $config['by'];
        }

        $needs_resizing = false;

        if (!isset($config['upscale']) || $config['upscale']) {
            $needs_resizing = true;
        } else {
            switch($by) {
                default:
                case self::RESIZE_BY_HEIGHT:
                    $needs_resizing = $height > $config['height'];
                    break;
                case self::RESIZE_BY_WIDTH:
                    $needs_resizing = $width > $config['width'];
                    break;
                case self::RESIZE_COVER:
                    $needs_resizing = true;
                    break;
            }
        }

        if ($needs_resizing) {

            $src_x = $src_y = 0;

            switch($by) {
                default:
                case self::RESIZE_BY_HEIGHT:
                    $thumb_h = $config['height'];
                    $thumb_w = $config['height'] * $aspect;
                    break;
                case self::RESIZE_BY_WIDTH:
                    $thumb_w = $config['width'];
                    $thumb_h = $config['width'] / $aspect;
                    break;
                case self::RESIZE_COVER:
                    $thumb_w = $config['width'];
                    $thumb_h = $config['height'];

                    // calculate crop x,y
                    $new_aspect = $config['width'] / $config['height'];

                    if ($new_aspect < $aspect) { // if thumb is narrower than original image -> crop width
                        $src_x = ($width - $height*$new_aspect)/2;
                        $width = $height*$new_aspect;
                        $src_y = 0;
                    } else { // image is wider than original
                        $src_x = 0;
                        $src_y = ($height - $width/$new_aspect)/2;
                        $height = $width/$new_aspect;
                    }

                    break;
            }

            $dst_image = imagecreatetruecolor($thumb_w, $thumb_h);

            imagecopyresampled($dst_image, $image, 0, 0, $src_x, $src_y, $thumb_w, $thumb_h, $width, $height);

            imagedestroy($image);

            return $dst_image;

        } else {
            return $image;
        }
    }

    public static function crop($image, $config)
    {

        $width = imageSX($image);
        $height = imageSY($image);
        $aspect = $width / $height;

        if ($aspect !== $config['aspect']) {
            $src_x = $src_y = 0;

            // calculate crop x,y
            if ($config['aspect'] < $aspect) { // if thumb is narrower than original image -> crop width
                $src_x = ($width - $height*$config['aspect'])/2;
                $width = $height*$config['aspect'];
                $src_y = 0;
            } else { // image is wider than original
                $src_x = 0;
                $src_y = ($height - $width/$config['aspect'])/2;
                $height = $width/$config['aspect'];
            }

            $dst_image = imagecreatetruecolor($thumb_w, $thumb_h);

            imagecopyresampled($dst_image, $image, 0, 0, $src_x, $src_y, $thumb_w, $thumb_h, $width, $height);

            imagedestroy($image);

            return $dst_image;

        } else {
            return $image;
        }
    }

    public static function resizeFile($from, $to, &$resize, $format) //&$new_w, &$new_h, $by = self::RESIZE_BY_HEIGHT, $upscale = true)
    {

        list($src_img, $ext) = self::open($from);

        // // fix orientation
        // if ($ext === 'jpeg') {
        //     $exif = @exif_read_data($from);

        //     if(!empty($exif['Orientation'])) {
        //         switch($exif['Orientation']) {
        //             case 3: // upside down
        //                 $src_img = imagerotate($src_img, 180, 0);
        //                 break;
        //             case 6: // rotated 90° right
        //                 $src_img = imagerotate($src_img, -90, 0);
        //                 break;
        //             case 8: // rotated 90° left
        //                 $src_img = imagerotate($src_img, 90, 0);
        //                 break;
        //         }
        //     }
        // }

        if ($format === false) {
            $format = $ext;
        } else {
            $info = pathinfo($to);
            $to = $info['dirname'] . '/' . $info['filename'] . '.' . $format;
        }

        //
        $width = imageSX($src_img);
        $height = imageSY($src_img);
        $aspect = $width / $height;

        if ($resize['by'] === self::RESIZE_CONTAIN) {
            $new_aspect = $resize['width']/$resize['height'];

            $by = $aspect > $new_aspect ? self::RESIZE_BY_WIDTH : self::RESIZE_BY_HEIGHT;
        } else {
            $by = $resize['by'];
        }

        //
        $needs_resizing = false;

        if (!isset($resize['upscale']) || $resize['upscale']) {
            $needs_resizing = true;
        } else {
            switch($by) {
                default:
                case self::RESIZE_BY_HEIGHT:
                    $needs_resizing = $height > $resize['height'];
                    break;
                case self::RESIZE_BY_WIDTH:
                    $needs_resizing = $width > $resize['width'];
                    break;
                case self::RESIZE_COVER:
                    $needs_resizing = true;
                    break;
            }
        }

        //
        if ($needs_resizing) {

            $src_x = $src_y = 0;

            switch($by) {
                default:
                case self::RESIZE_BY_HEIGHT:
                    $thumb_h = $resize['height'];
                    $thumb_w = $resize['height'] * $aspect;
                    break;
                case self::RESIZE_BY_WIDTH:
                    $thumb_w = $resize['width'];
                    $thumb_h = $resize['width'] / $aspect;
                    break;
                case self::RESIZE_COVER:
                    $thumb_w = $resize['width'];
                    $thumb_h = $resize['height'];

                    // calculate crop x,y
                    $new_aspect = $resize['width'] / $resize['height'];

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
                imagealphablending($dst_img, false);
                imagesavealpha($dst_img, true);
                // $black = imagecolorallocate($dst_img, 0, 0, 0);
                // imagecolortransparent($dst_img, $black);
            }

            imagecopyresampled($dst_img, $src_img, 0, 0, $src_x, $src_y, $thumb_w, $thumb_h, $width, $height);

            $result = self::save($dst_img, $format, $to);

            imagedestroy($dst_img);

        } else {
            $result = self::save($src_img, $format, $to);
            $thumb_w = $width;
            $thumb_h = $height;
        }

        imagedestroy($src_img);

        $resize['width'] = $thumb_w;
        $resize['height'] = $thumb_h;

        return $result;
    }
}
