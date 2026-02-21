<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
class ShopController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }



        public function index(Request $request)
        {
            $role = null;

            try {
                // First try header token
                if ($request->bearerToken()) {
                    $user = JWTAuth::parseToken()->authenticate();
                    $role = JWTAuth::parseToken()->getPayload()->get('role');
                }
                // If no header, check session token
                elseif (session('jwt_token')) {
                    $user = JWTAuth::setToken(session('jwt_token'))->authenticate();
                    $role = JWTAuth::setToken(session('jwt_token'))->getPayload()->get('role');
                }
            } catch (\Exception $e) {
                $role = null;
            }

            $products = $this->productService->getShopProducts($role);

            return response()->json([
                'role' => $role ?? 'guest',
                'count' => count($products),
                'data' => $products
            ]);
        }

    public function show(Request $request, $id)
    {
        $role = null;

        try {
            if ($request->bearerToken()) {
                $user = JWTAuth::parseToken()->authenticate();
                $role = JWTAuth::parseToken()->getPayload()->get('role');
            } elseif (session('jwt_token')) {
                $user = JWTAuth::setToken(session('jwt_token'))->authenticate();
                $role = JWTAuth::setToken(session('jwt_token'))->getPayload()->get('role');
            }
        } catch (\Exception $e) {
            $role = null;
        }

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