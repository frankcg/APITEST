<?php

namespace App\Http\Controllers\Seller;

use App\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class SellerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vendedores = Seller::has('products')->get();
        //dd($vendedores);
        //return response()->json(['data'=>$vendedores],200);
        return $this->showAll($vendedores);
    }

    
    public function show($id)
    {
        $vendedor = Seller::has('products')->findOrFail($id);
        return $this->showOne($vendedor);
    }

}
