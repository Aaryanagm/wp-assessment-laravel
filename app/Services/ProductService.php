<?php

namespace App\Services;

use App\Models\WpPost;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getShopProducts($role = null)
    {
        $products = WpPost::where('post_type', 'product')
            ->where('post_status', 'publish')
            ->with([
                'meta',
                'variations.meta',
                'termRelationships.taxonomy.term'
            ])
            ->get();

        // Collect all term_ids at once
        $termIds = [];

        foreach ($products as $product) {
            foreach ($product->termRelationships as $relation) {
                if ($relation->taxonomy && $relation->taxonomy->taxonomy === 'product_cat') {
                    $termIds[] = $relation->taxonomy->term_id;
                }
            }
        }

        $termIds = array_unique($termIds);

        // Fetch visibility for all categories in one query
        $visibilityMap = \DB::table('wp_termmeta')
            ->whereIn('term_id', $termIds)
            ->where('meta_key', 'category_visibility')
            ->pluck('meta_value', 'term_id')
            ->toArray();

        return $products->filter(function ($product) use ($role, $visibilityMap) {

            $isProtected = false;

            foreach ($product->termRelationships as $relation) {

                if ($relation->taxonomy && $relation->taxonomy->taxonomy === 'product_cat') {

                    $termId = $relation->taxonomy->term_id;

                    $visibility = $visibilityMap[$termId] ?? 'public';

                    if ($visibility === 'protected') {
                        $isProtected = true;
                    }
                }
            }

            if (!$role && $isProtected) {
                return false;
            }

            return true;

        })->map(function ($product) use ($role) {

            return [
                'id' => $product->ID,
                'title' => $product->post_title,
                'description' => $product->post_content,
                'price' => $this->resolvePrice($product, $role),
                'stock' => $this->getMetaValue($product, '_stock'),
                'variants' => $product->variations->map(function ($variation) use ($role) {
                    return [
                        'id' => $variation->ID,
                        'price' => $this->resolvePrice($variation, $role),
                        'stock' => $this->getMetaValue($variation, '_stock')
                    ];
                })
            ];
        })->values();
    }

    private function getCategoryVisibility($product)
    {
        foreach ($product->termRelationships as $relation) {

            $taxonomy = $relation->taxonomy;

            if ($taxonomy && $taxonomy->taxonomy === 'product_cat') {

                $meta = DB::table('wp_termmeta')
                    ->where('term_id', $taxonomy->term_id)
                    ->where('meta_key', 'category_visibility')
                    ->first();

                return $meta ? $meta->meta_value : 'public';
            }
        }

        return 'public';
    }

    private function formatProduct($product, $role)
    {
        return [
            'id' => $product->ID,
            'title' => $product->post_title,
            'description' => $product->post_content,
            'price' => $this->resolvePrice($product, $role),
            'stock' => $this->getMetaValue($product, '_stock'),
            'variants' => $product->variations->map(function ($variation) use ($role) {
                return [
                    'id' => $variation->ID,
                    'price' => $this->resolvePrice($variation, $role),
                    'stock' => $this->getMetaValue($variation, '_stock'),
                ];
            })
        ];
    }

    private function resolvePrice($model, $role)
    {
        if (!$role) {
            return $this->getMetaValue($model, '_customer_price');
        }

        return match ($role) {
            'gold', 'administrator' => $this->getMetaValue($model, '_gold_price'),
            'silver' => $this->getMetaValue($model, '_silver_price'),
            default => $this->getMetaValue($model, '_customer_price'),
        };
    }

    private function getMetaValue($model, $key)
    {
        static $cache = [];

        $modelKey = $model->ID . '_' . $key;

        if (isset($cache[$modelKey])) {
            return $cache[$modelKey];
        }

        $meta = $model->meta->firstWhere('meta_key', $key);
        $value = $meta ? $meta->meta_value : null;

        $cache[$modelKey] = $value;

        return $value;
    }

    public function getSingleProduct($id, $role = null)
    {
        $product = WpPost::where('post_type', 'product')
            ->where('post_status', 'publish')
            ->where(function ($q) use ($id) {
                $q->where('ID', $id)
                ->orWhere('post_name', $id);
            })
            ->first();

        if (!$product) {
            return null;
        }

        // ------------------------------
        // Collect category term IDs
        // ------------------------------
        $termIds = [];

        foreach ($product->termRelationships as $relation) {
            if (
                $relation->taxonomy &&
                $relation->taxonomy->taxonomy === 'product_cat'
            ) {
                $termIds[] = $relation->taxonomy->term_id;
            }
        }

        $termIds = array_unique($termIds);

        // ------------------------------
        // Fetch visibility in ONE query
        // ------------------------------
        $visibilityMap = [];

        if (!empty($termIds)) {
            $visibilityMap = \DB::table('wp_termmeta')
                ->whereIn('term_id', $termIds)
                ->where('meta_key', 'category_visibility')
                ->pluck('meta_value', 'term_id')
                ->toArray();
        }

        // ------------------------------
        // Check if product is protected
        // ------------------------------
        $isProtected = false;

        foreach ($termIds as $termId) {
            if (($visibilityMap[$termId] ?? 'public') === 'protected') {
                $isProtected = true;
                break;
            }
        }

        // Guest cannot access protected product
        if (!$role && $isProtected) {
            return null;
        }

        // ------------------------------
        // Build category response
        // ------------------------------
        $categories = [];

        foreach ($product->termRelationships as $relation) {

            if (
                $relation->taxonomy &&
                $relation->taxonomy->taxonomy === 'product_cat'
            ) {
                $term = $relation->taxonomy->term;

                $categories[] = [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'visibility' => $visibilityMap[$term->term_id] ?? 'public'
                ];
            }
        }

        // ------------------------------
        // Build final response
        // ------------------------------
        return [
            'id' => $product->ID,
            'title' => $product->post_title,
            'description' => $product->post_content,
            'categories' => $categories,
            'pricing' => $this->getPricingStructure($product, $role),
            'stock' => $this->getMetaValue($product, '_stock'),
            'variants' => $product->variations->map(function ($variation) use ($role) {
                return [
                    'id' => $variation->ID,
                    'pricing' => $this->getPricingStructure($variation, $role),
                    'stock' => $this->getMetaValue($variation, '_stock')
                ];
            })->values()
        ];
    }

    private function formatSingleProduct($product, $role)
    {
        $categories = [];

        foreach ($product->termRelationships as $relation) {

            $taxonomy = $relation->taxonomy;

            if ($taxonomy && $taxonomy->taxonomy === 'product_cat') {

                $visibilityMeta = \DB::table('wp_termmeta')
                    ->where('term_id', $taxonomy->term_id)
                    ->where('meta_key', 'category_visibility')
                    ->first();

                $categories[] = [
                    'id' => $taxonomy->term->term_id,
                    'name' => $taxonomy->term->name,
                    'slug' => $taxonomy->term->slug,
                    'visibility' => $visibilityMeta
                        ? $visibilityMeta->meta_value
                        : 'public'
                ];
            }
        }

        return [
            'id' => $product->ID,
            'title' => $product->post_title,
            'description' => $product->post_content,
            'categories' => $categories,
            'pricing' => $this->getPricingStructure($product, $role),
            'stock' => $this->getMetaValue($product, '_stock'),
            'variants' => $product->variations->map(function ($variation) use ($role) {
                return [
                    'id' => $variation->ID,
                    'pricing' => $this->getPricingStructure($variation, $role),
                    'stock' => $this->getMetaValue($variation, '_stock'),
                ];
            })
        ];
    }

    private function getPricingStructure($model, $role)
    {
        $customer = $this->getMetaValue($model, '_customer_price');
        $silver   = $this->getMetaValue($model, '_silver_price');
        $gold     = $this->getMetaValue($model, '_gold_price');

        if (!$role) {
            return [
                'current_price' => $customer
            ];
        }

        return [
            'current_price' => $this->resolvePrice($model, $role),
            'customer_price' => $customer,
            'silver_price' => $silver,
            'gold_price' => $gold
        ];
    }
}