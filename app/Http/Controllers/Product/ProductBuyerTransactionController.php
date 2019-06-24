<?php

namespace App\Http\Controllers\Product;

use App\User;
use App\Product;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use Illuminate\Database\Concerns\transaction as TransactionDatabase;



class ProductBuyerTransactionController extends ApiController
{
    public function store(Request $request, Product $product, User $buyer)
    {
        $rules=[
            'quantity' => 'required|integer|min:1',
        ];

        $this->validate($request,$rules);

        if($buyer->id == $product->seller_id){
            return $this->errorResponse('El comprador debe ser diferente al vendedor',409);
        }

        /*if(!$buyer->esVerificado()){
            return $this->errorResponse('El comprador debe ser un usuario verificado',409);
        }*/

        /*if(!$product->seller->esVerificado()){
            return $this->errorResponse('El vendedor debe ser un usuario verificado',409);
        }*/

        if(!$product->estadoDisponible()){
            return $this->errorResponse('El producto para la transaccion no esta Disponible',409);
        }

        if($product->quantity < $request->quantity){
            return $this->errorResponse('El producto no tiene la cantidad requerida para esta transaccion',409); 
        }

        return DB::transaction(function()use($request,$product,$buyer){

            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity'=>$request->quantity,
                'buyer_id'=>$buyer->id,
                'product_id'=>$product->id,
            ]);

            return $this->showOne($transaction,201);

        });
    }
}
