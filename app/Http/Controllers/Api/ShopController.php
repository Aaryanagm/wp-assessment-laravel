<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;

class ShopController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        // If logged in → get role from JWT
        $role = auth('api')->check()
            ? auth('api')->payload()->get('role')
            : null;

        $products = $this->productService->getShopProducts($role);

        return response()->json([
            'role' => $role ?? 'guest',
            'count' => $products->count(),
            'data' => $products
        ]);
    }

    public function show($id)
    {
        $role = auth('api')->check()
            ? auth('api')->payload()->get('role')
            : null;

        $product = $this->productService->getSingleProduct($id, $role);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'role' => $role ?? 'guest',
            'data' => $product
        ]);
    }
}