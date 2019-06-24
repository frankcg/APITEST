<?php

namespace App\Http\Controllers\User;

use App\User;
use App\Mail\UserCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{    
    public function index()
    {
        $usuarios = User::all();
        return $this->showAll($usuarios);
    }
   
    public function store(Request $request)
    {
        $reglas = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ];

        $this->validate($request,$reglas);
        $campos = $request->all();
        $campos['password'] = bcrypt($request->password);
        $campos['verified'] = User::USUARIO_NO_VERIFICADO;
        $campos['verification_token'] = User::generarVerificationToken();
        $campos['admin'] = User::USUARIO_REGULAR;
       
        $usuario = User::create($campos);
        //return response()->json(['data'=>$usuario],201);
        return $this->showOne($usuario,201);
    }

    public function show($id)
    {
        $usuario = User::findOrFail($id);
        return $this->showOne($usuario,201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);        

        $reglas = [
            'email' => 'email|unique:users,email,'.$user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:'.User::USUARIO_ADMINISTRADOR.','.User::USUARIO_REGULAR
        ];

        $this->validate($request,$reglas);
        
        if($request->has('name')){
            $user->name = $request->name;
        }

        if($request->has('email') && $user->email != $request->email){
            $user->verified = User::USUARIO_NO_VERIFICADO;
            $user->verification_token = User::generarVerificationToken();
            $user->email = $request->email;
        }

        if($request->has('password')){
            $user->password = bcrypt($user->password);
        }

        if($request->has('admin')){
            if(!$user->esVerificado()){
                return $this->errorResponse('Unicamente los usuarios verificados pueden ser administradores',409);
            }
            $user->admin = $request->admin;
        }

        if(!$user->isDirty()){
            return $this->errorResponse('Se debe especificar almenos un valor diferente', 422);
        }

        $user->save();
        return $this->showOne($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return $this->showOne($user);
    }

    public function verify($token)
    {

        $user = User::where('verification_token',$token)->firstOrFail();

        $user->verified = User::USUARIO_VERIFICADO;
        $user->verification_token = null;

        $user->save();

        return $this->showMessage('La cuenta ha sido verificada');
        //return view('frontEmail');//rendiraz a una vista
    }

    public function resend(User $user)
    {
        if($user->esVerificado()){
            return $this->errorResponse('Este usuario ya ha sido verificado.',409);            
        }
        Mail::to($user)->send(new UserCreated($user));

        return $this->showMessage('El correo de verifiacion se ha reenviado');
    }   
}
