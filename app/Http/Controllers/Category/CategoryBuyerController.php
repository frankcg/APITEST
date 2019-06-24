<?php

namespace App\Http\Controllers\Category;

use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class CategoryBuyerController extends ApiController
{
    public function index(Category $category)
    {
        //$buyers = $category->products()->select('description','name','id')->get();
        $buyers = $category->products()
                ->whereHas('transactions')
                ->with('transactions.buyer')
                ->get()
                ->pluck('transactions')
                ->collapse()
                ->pluck('buyer')
                ->unique()
                ->values();

        return $this->showAll($buyers);
    }
}
