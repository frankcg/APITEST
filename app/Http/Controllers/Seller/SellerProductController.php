<?php

namespace App\Http\Controllers\Seller;

use App\User;
use App\Seller;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    public function index(Seller $seller)
    {
        $products = $seller->products;
        return $this->showAll($products);
    }

    public function store(Request $request, User $seller)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            //'image' => 'required|image',
        ];
        $this->validate($request,$rules);
        $data = $request->all();

        $data['status'] = Product::PRODUCTO_NO_DISPONIBLE;
        //$data['image'] = '1.jpg';
        $data['image'] = $request->image->store('');
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);
        return $this->showOne($product,201);
    }

    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules = [
            'quantity' => 'integer|min:1',
            'status' => 'in: '. Product::PRODUCTO_DISPONIBLE .','. Product::PRODUCTO_NO_DISPONIBLE,
            //'image' => 'image',
        ];

        $this->validate($request,$rules);
        $this->verificarVendedor($seller, $product);

        $product->fill($request->only([
            'name','description','quantity',
        ]));

        if($request->has('status')){
            $product->status = $request->status;
            if($product->estadoDisponible() && $product->categories()->count()==0){
                return $this->errorResponse('Un producto activo debe tener almenos una categoria',409);
            }
        }

        if($request->hasfile('image')){
            Storage::delete($product->image);
            $product->image = $request->image->store('');
        }

        if($product->isClean()){
            return $this->errorResponse('Se debe especificar almenos un valor diferente para actualizar',422);
        }

        $product->save();
        return $this->showOne($product);        
    }

    
    public function destroy(Seller $seller, Product $product)
    {
        $this->verificarVendedor($seller, $product);

        Storage::delete($product->image);

        $product->delete();
        return $this->showOne($product);
    }

    protected function verificarVendedor(Seller $seller, Product $product){
        if($seller->id != $product->seller_id){
            throw new HttpException(422,'El vendedor especificado no es el vendedor del producto');
        }
    }
}
