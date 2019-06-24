<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\resolveCurrentPage;
use Illuminate\Pagination\LengthAwarePaginator;


trait ApiResponser{

	private function successResponse($data,$code){
		return response()->json($data,$code);
	}

	protected function errorResponse($message, $code){
		return response()->json(['error'=>$message,'code'=>$code],$code); 
	}

	protected function showAll(Collection $collection, $code=200){
		$collection = $this->paginate($collection);
		//return $this->successResponse(['data'=>$collection],$code);
		return $this->successResponse($collection,$code); 
	}

	protected function showOne(Model $instance, $code=200){
		return $this->successResponse(['data'=>$instance],$code); 
	}

	protected function showMessage($message, $code=200){
		return $this->successResponse(['data'=>$message],$code); 
	}

	protected function paginate(Collection $collection){
		//http://localhost:9090/APIRestFul/public/api/users?per_page=18&page=2
		$rules=[
			'per_page' => 'integer|min:2|max:50'
		];

		Validator::validate(request()->all(),$rules);

		$page = LengthAwarePaginator::resolveCurrentPage();

		$perPage = 15;
		if(request()->has('per_page')){
			$perPage = (int) request()->per_page;
		}
		$results = $collection->slice(($page-1)*$perPage, $perPage)->values();
		$paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page,[
			'path' => LengthAwarePaginator::resolveCurrentPage(),
		]);

		$paginated->appends(request()->all());

		return $paginated;
	}

}