<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\Utils\WpPost;

class WpPostController
{
    use WpPost;

    public function getPostTypes()
    {
        $args = [
            'public' => true,
        ];
        $postTypes = get_post_types($args, 'objects');

        $postTypes = array_map(function ($post_type) {
            return [
                'name'  => $post_type->name,
                'label' => $post_type->label,
            ];
        }, $postTypes);

        return Response::success($postTypes);
    }

    public function getCategoriesAndTags(Request $request)
    {
        $validatedData = $request->validate([
            'postType' => ['string']
        ]);

        $postType = $validatedData['postType'];

        $allTaxonomiesTerms = [];
        $taxonomyData = [];

        $taxonomies = get_object_taxonomies($postType, 'objects');

        // Collect public taxonomy data (key: label, value: name)
        foreach ($taxonomies as $taxonomy) {
            if (!$taxonomy->public) {
                continue;
            }
            $taxonomyData[$taxonomy->label] = $taxonomy->name;
        }

        if (!empty($taxonomyData)) {
            // Get all terms for the collected taxonomy names
            $all_terms = get_terms([
                'taxonomy'   => array_values($taxonomyData),
                'hide_empty' => false,
            ]);

            // Organize terms by taxonomy label if terms are found

            if (!empty($all_terms) && !is_wp_error($all_terms)) {
                foreach ($all_terms as $term) {
                    // Get the label for the taxonomy
                    $taxonomyLabel = array_search($term->taxonomy, $taxonomyData);

                    $allTaxonomiesTerms[$taxonomyLabel][] = [
                        'id'   => $term->term_id,
                        'name' => $term->name,
                    ];
                }
            }
        }

        return Response::success($allTaxonomiesTerms);
    }

    public function getFilteredPosts(Request $request)
    {
        $validatedData = (object) $request->validate([
            'filter_by_days'      => ['nullable'],
            'custom_date_range'   => ['nullable'],
            'post_type'           => ['nullable'],
            'categories_and_tags' => ['nullable'],
            'specific_postIds'    => ['nullable', 'array']

        ]);

        $filterOptions = (object) $validatedData;
        $posts = $this->filterPosts($filterOptions);

        $filteredPosts = array_map(function ($post) {
            return [
                'value' => $post['id'],
                'label' => "{$post['id']}: {$post['name']}",
            ];
        }, $posts);

        return Response::success($filteredPosts);
    }

    public function filterPosts($filterOptions)
    {
        $filter = [];

        if (!empty($filterOptions->post_type)) {
            $filter['post_type'] = $filterOptions->post_type;
        }

        if (isset($filterOptions->categories_and_tags) && is_numeric($filterOptions->categories_and_tags)) {
            $filter['tax_query'] = $this->categoryAndTags($filterOptions->categories_and_tags);
        }

        if (!empty($filterOptions->custom_date_range)) {
            $filter['date_query'] = [
                'after'     => $filterOptions->custom_date_range[0],
                'before'    => $filterOptions->custom_date_range[1],
                'inclusive' => true,
            ];
        }

        return $this->getPosts($filter);
    }

    public function categoryAndTags($categoriesAndTags)
    {
        $term = get_term($categoriesAndTags);
        if ($term && !is_wp_error($term)) {
            $taxonomy = $term->taxonomy;

            return [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $categoriesAndTags,
                ],
            ];
        }
    }
}
