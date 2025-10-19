<?php

namespace BitApps\Social\Utils;

trait PostInfo
{
    public static function getFeaturedImageUrl($postId)
    {
        if (has_post_thumbnail($postId)) {
            return get_the_post_thumbnail_url($postId, 'full');
        }
    }

    public function getContentShort($content, $length)
    {
        if (\strlen($content) > $length) {
            return substr($content, 0, $length);
        }

        return $content;
    }

    public static function getAllImages($postId)
    {
        $postType = get_post_type($postId);
        if ($postType === 'product') {
            $product = wc_get_product($postId);

            $attachmentIds = $product->get_gallery_image_ids();
            $featuredImageId = $product->get_image_id();

            if ($featuredImageId) {
                array_unshift($attachmentIds, (int) $featuredImageId);
            }

            $allImagesUrl = [];
            foreach ($attachmentIds as $attachmentId) {
                $imageUrl = wp_get_attachment_url($attachmentId);
                if ($imageUrl) {
                    $allImagesUrl[] = $imageUrl;
                }
            }

            return $allImagesUrl;
        }

        $post = get_post($postId);
        $post_content = $post->post_content;

        $pattern = '/<img.*?src="([^"]*)"/';

        $matches = [];

        preg_match_all($pattern, $post_content, $matches);

        $allImagesUrl = $matches[1];

        $featuredImageUrl = self::getFeaturedImageUrl($postId);

        if ($featuredImageUrl) {
            array_unshift($allImagesUrl, $featuredImageUrl);
        }

        return $allImagesUrl;
    }

    public function getVideo($post_id)
    {
        $post_content = get_post($post_id)->post_content;

        preg_match('/<figure.*src=\"(.*)\".*><\/figure>/iU', $post_content, $matches);

        return !empty($matches[1]) ? $matches[1] : '';
    }
}
