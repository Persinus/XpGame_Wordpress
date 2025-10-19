<?php

namespace BitApps\Social\HTTP\Services\Social;

use BitApps\Social\Config;
use BitApps\Social\HTTP\Services\Social\LinkedinService\Helper;

class SocialValidator
{
    public static $platform;

    public function __construct($platform)
    {
        self::$platform = $platform;
    }

    public static function validatePlatform($file)
    {
        $root_dir = Config::get('ROOT_DIR');
        $platformLimitationPath = $root_dir . '/config/platformsLimitations.json';

        if (file_exists($platformLimitationPath)) {
            $jsonData = file_get_contents($platformLimitationPath);
            $platformLimitations = json_decode($jsonData, true);
            $linkedinLimit = $platformLimitations[self::$platform];
        }

        if (\is_array($file)) {
            $fileType = Helper::getFileType($file[0]);
        } else {
            $fileType = Helper::getFileType($file);
        }

        if (empty($linkedinLimit) || empty($fileType)) {
            return ['status' => false, 'message' => 'Cant get file type or limitation'];
        }

        if (strpos($fileType, 'video') !== false) {
            return self::verifyVideo($file, $linkedinLimit);
        } elseif (strpos($fileType, 'image') !== false) {
            return self::verifyImage($file, $linkedinLimit);
        }

        return false;
    }

    public static function verifyVideo($video, $linkedinLimit)
    {
        $minSize = $linkedinLimit['video']['size']['min-value'];
        $maxSize = $linkedinLimit['video']['size']['max-value'];
        $minDuration = $linkedinLimit['video']['length']['min-value'] * 60;
        $maxDuration = $linkedinLimit['video']['length']['max-value'] * 60;

        $videoPostId = attachment_url_to_postid($video);
        $information = wp_get_attachment_metadata($videoPostId);

        // video size convert to MB
        $videoSize = $information['filesize'] / 1048576;

        if ($minSize < $videoSize && $videoSize < $maxSize) {
            return ['status' => true, 'data' => $video];
        }

        return ['status' => false, 'data' => 'Video is not valid.'];
    }

    public static function verifyImage($image, $linkedinLimit)
    {
        $imageDimension = $linkedinLimit['image']['dimension'];
        $validImageExt = $linkedinLimit['image']['ext'];
        $validGifFrame = $linkedinLimit['gif']['frame'];
        if (\is_array($image)) {
            foreach ($image as $singleImage) {
                $isValidExt = self::validateImageTypeAndExt($singleImage, $validImageExt);
                if (!$isValidExt) {
                    continue;
                }

                if ($isValidExt !== 'gif') {
                    $imageSize = self::imagePixel($singleImage);
                    if ($imageDimension >= $imageSize) {
                        $all_images[] = $singleImage;
                    }
                } else {
                    $all_images[] = $singleImage;
                }
            }

            if (!empty($all_images)) {
                return ['status' => true, 'data' => $all_images];
            }

            return ['status' => false, 'message' => 'All image and Gif are not valid'];
        }

        $isValidExt = self::validateImageTypeAndExt($image, $validImageExt);
        if (!$isValidExt) {
            return [
                'status'  => false,
                'message' => 'Extension is not valid'
            ];
        }

        if ($isValidExt !== 'gif') {
            $imageSize = self::imagePixel($image);
            if ($imageDimension >= $imageSize) {
                return ['status' => true, 'data' => $image];
            }

            return ['status' => false, 'message' => 'Image size or Dimension is not valid'];
        }
        $gifFrames = self::gifFrameCount($image);

        return ['status' => true, 'data' => $image];
    }

    public static function imagePixel($image)
    {
        $attachmentPostId = attachment_url_to_postid($image);
        $imageInfo = wp_get_attachment_metadata($attachmentPostId);

        $imageWidth = $imageInfo['width'];
        $imageHeight = $imageInfo['height'];

        return $imageWidth * $imageHeight;
    }

    public static function validateImageTypeAndExt($image, $validImageExt)
    {
        $attachmentPostId = attachment_url_to_postid($image);
        $imageInfo = wp_get_attachment_metadata($attachmentPostId);
        $imageMemeType = $imageInfo['sizes']['medium']['mime-type'];

        if ($imageInfo !== false) {
            $imageExt = explode('/', $imageMemeType)[1];

            if (\in_array($imageExt, $validImageExt)) {
                return $imageExt;
            }
        }

        return false;
    }

    public static function gifFrameCount($singleImage)
    {
        return $singleImage;

        $attachmentPostId = attachment_url_to_postid($singleImage);
        $imageInfo = wp_get_attachment_metadata($attachmentPostId);

        if ($imageInfo && $imageInfo['mime'] === 'image/gif' && isset($imageInfo['pages'])) {
            return $imageInfo['pages'];
        }

        return false;
    }
}
