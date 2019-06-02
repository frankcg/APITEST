<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class BuyerController extends ApiController
{
    public function index()
    {
        $compradores = Buyer::has('transactions')->get();
        return $this->showAll($compradores);
    }

    public function show($id)
    {
        $comprador = Buyer::has('transactions')->findOrFail($id);
        //return response()->json(['data'=>$compradores],200);
        return $this->showOne($comprador);
    }
}
