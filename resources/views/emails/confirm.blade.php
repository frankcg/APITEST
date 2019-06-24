Hola {{$user->name}}
Has cambiado tu cuenta de correo. Por favor verificalo usando el siguiente enlace:


{{route('verify',$user->verification_token)}}