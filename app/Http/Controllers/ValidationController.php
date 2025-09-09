<?php

namespace App\Http\Controllers;

use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UsuariosBiometricos;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Twilio\Rest\Client;


class ValidationController extends Controller
{
    //con codigo de verificacion -- no usado actualmente
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'identificacion' => 'required|string|max:20',
            Rule::unique('informacion_votantes')->where(function ($query) {
                return $query->whereNotNull('comuna')->where('comuna', '!=', 0);
            }),
            'tipo_documento' => 'required|string',
            'nacimiento' => 'required|date',
            'genero' => 'required|string',
            'etnia' => 'required|string',
            'condicion' => 'required|string',
            'comuna' => 'required',
            'barrio' => 'string',
            'direccion' => 'string',
            // 'email' => 'required|email|unique:users',
            'email' => 'required|email',
            // 'celular' => 'required|string|max:15|unique:users,celular,NULL,id,estado,Activo|regex:/^\d{10,15}$/', // Validación para números de celular
            'celular' => 'required|string|max:15|regex:/^\d{10,15}$/', // Validación para números de celular
            'password' => 'required|string|min:8',
            'recaptcha_token' => 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $posibleSpam = false;
        // Verifica el token de reCAPTCHA
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => env('RECAPTCHA_SECRET_KEY'), // Llave secreta de reCAPTCHA
                'response' => $request->recaptcha_token,
            ]);

            $recaptchaData = $response->json();



            if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.3) {
                if ($request->campoObligatorio != null && $request->campoObligatorio != '') {
                    return response()->json(['errors' => 'La validación de reCAPTCHA falló. Inténtelo nuevamente.'], 422);
                } else {
                    $posibleSpam = true;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al verificar reCAPTCHA: ' . $e->getMessage());
            return response()->json(['errors' => 'Error al verificar reCAPTCHA. Por favor, inténtelo nuevamente.'], 500);
        }

        // Generar un código de verificación
        $verificationCode = rand(100000, 999999);

        // Guardar el código en la base de datos
        VerificationCode::create([
            'email' => $request->email,
            'code' => $verificationCode,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Enviar el código por correo o SMS
        if ($request->has('via') && $request->via === 'sms') {
            try {
                $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

                $twilio->messages->create(
                    '+57' . $request->celular, // Número de destino
                    [
                        'from' => env('TWILIO_PHONE_NUMBER'), // Número de Twilio
                        'body' => "Tu código de verificación es: $verificationCode",
                    ]
                );
            } catch (\Exception $e) {
                return response()->json(['message' => 'Error al enviar el SMS: ' . $e->getMessage()], 500);
            }
        } else {
            // Enviar por correo
            Mail::send('emails.codigo_verificacion', ['code' => $verificationCode], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Código de Verificación');
            });
        }

        return response()->json(['message' => 'Código de verificación enviado.', 'isSpam' => $posibleSpam], 200);
    }

    public function verify(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'email' => 'required|email',
        //     'codigo' => 'required|integer',
        //     'checked' => 'required',
        //     'declaracion' => 'required',
        // ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'identificacion' => 'required|string|max:20',
            Rule::unique('informacion_votantes')->where(function ($query) {
                return $query->whereNotNull('comuna')->where('comuna', '!=', 0);
            }),
            'tipo_documento' => 'required|string',
            'nacimiento' => 'required|date',
            'genero' => 'required|string',
            'etnia' => 'required|string',
            'condicion' => 'required|string',
            'comuna' => 'required',
            'barrio' => 'string',
            'direccion' => 'string',
            // 'email' => 'required|email|unique:users',
            'email' => 'nullable|email',
            // 'celular' => 'required|string|max:15|unique:users,celular,NULL,id,estado,Activo|regex:/^\d{10,15}$/', // Validación para números de celular
            'celular' => 'required|string|max:15|regex:/^\d{10,15}$/', // Validación para números de celular
            'password' => 'required|string|min:8',
            'recaptcha_token' => 'required|string',
            'cedula_front' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'checked' => 'required',
            'declaracion' => 'required',

        ]);




        $posibleSpam = false;
        // Verifica el token de reCAPTCHA
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => env('RECAPTCHA_SECRET_KEY'), // Llave secreta de reCAPTCHA
                'response' => $request->recaptcha_token,
            ]);

            $recaptchaData = $response->json();



            if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.3) {
                if ($request->campoObligatorio != null && $request->campoObligatorio != '') {
                    return redirect()->back()->withErrors(['error' => 'La validación de reCAPTCHA falló. Inténtelo nuevamente.']);
                } else {
                    $posibleSpam = true;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al verificar reCAPTCHA: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Error al verificar reCAPTCHA. Por favor, inténtelo nuevamente. ' . $e->getMessage()]);
        }



        // Crear el usuario
        try {

            // Obtener la extensión original de los archivos
            $frontExtension = $request->file('cedula_front')->getClientOriginalExtension();
            // $backExtension = $request->file('cedula_back')->getClientOriginalExtension();


            $folderDoc = 'documentos';
            $fileNameFront = 'cedula_front_' . $request->identificacion . '.' . $frontExtension;
            // $fileNameBack = 'cedula_back_' . $request->identificacion . '.' . $backExtension;
            // Guardar los archivos con su extensión original
            $frontPath = $request->file('cedula_front')->storeAs('uploads/' . $folderDoc, $fileNameFront, 'public');
            // $backPath = $request->file('cedula_back')->storeAs('uploads/' . $folderDoc, $fileNameBack, 'public');

            //foto evidencia
            $folderPhoto = 'fotos';
            $fileNamePhoto = 'foto' . $request->identificacion . '.' . $request->file('photo')->getClientOriginalExtension();

            $fotoPath = $request->file('photo')->storeAs('uploads/' . $folderPhoto, $fileNamePhoto, 'public');


            $validacion = $request->validaciones;
            // Log::info('embedding', ['embedding' => json_encode($request->embedding)]);

            // Crear el usuario
            $user = User::create([
                'name' => $request->nombre,
                'email' => 'ppt',
                'identificacion' => $request->identificacion,
                'password' => Hash::make($request->password),
                'estado' => 'Activo',

            ]);

            if ($request->embedding && ($validacion == "" || $posibleSpam)) {

                //CREAR REGISTRO BIOMETRICO
                $registroBiometrico = new UsuariosBiometricos([
                    'user_id' => $user->id,
                    'embedding' => json_encode($request->embedding), // convertir a string JSON
                    'photo' => $fileNamePhoto,
                    'cedula_front' => $fileNameFront,
                    // 'cedula_back' => $fileNameBack,
                    'motivo' => 'Validación exitosa',
                    'estado' => 'Validado',

                ]);

                if ($posibleSpam) {

                    $validacion = 'posible robot o spam';
                }
            } else {

                //CREAR REGISTRO BIOMETRICO no validado
                $registroBiometrico = new UsuariosBiometricos([
                    'user_id' => $user->id,
                    'embedding' => json_encode($request->embedding), // convertir a string JSON
                    'photo' => $fileNamePhoto,
                    'cedula_front' => $fileNameFront,
                    // 'cedula_back' => $fileNameBack,
                    'estado' => 'Pendiente',
                    'motivo' => $validacion,

                ]);
            }


            $user->biometrico()->save($registroBiometrico);

            $Informacion_votantes = Informacion_votantes::where('identificacion', $request->identificacion)
                ->whereHas('jurado')
                ->first();

            if ($Informacion_votantes) {

                $Informacion_votantes->nombre = $request->nombre;
                $Informacion_votantes->email = $request->email;
                $Informacion_votantes->id_user = $user->id;
                $Informacion_votantes->identificacion = $request->identificacion;
                $Informacion_votantes->tipo_documento = $request->tipo_documento;
                $Informacion_votantes->nacimiento = $request->nacimiento;
                $Informacion_votantes->genero = $request->genero;
                $Informacion_votantes->etnia = $request->etnia;
                $Informacion_votantes->condicion = $request->condicion;
                $Informacion_votantes->comuna = $request->input('comuna.value');
                $Informacion_votantes->barrio = $request->barrio;
                $Informacion_votantes->direccion = $request->direccion;
                $Informacion_votantes->celular = $request->celular;
                $Informacion_votantes->save();


                $hashVotante = Hash_votantes::where('id_votante', $Informacion_votantes->id)->first();

                    $hashVotante->tipo = 'votante';
                    $hashVotante->subtipo = $request->input('comuna.value');
                    $hashVotante->validaciones = $validacion;
                    $hashVotante->estado = 'Pendiente';
                    $hashVotante->intentos = 1;
                    $hashVotante->save();


            } else {
                // Crear la información del votante asociada al usuario
                $informacionUsuario = new Informacion_votantes([
                    'nombre' => $request->nombre,
                    'email' => $request->email,
                    'id_user' => $user->id,
                    'identificacion' => $request->identificacion,
                    'tipo_documento' => $request->tipo_documento,
                    'nacimiento' => $request->nacimiento,
                    'genero' => $request->genero,
                    'etnia' => $request->etnia,
                    'condicion' => $request->condicion,
                    'comuna' => $request->input('comuna.value'),
                    'barrio' => $request->barrio,
                    'direccion' => $request->direccion,
                    'celular' => $request->celular,

                ]);
                $user->votantes()->save($informacionUsuario);

                // Crear el registro de hash_votantes asociado a la información del votante
                $hash_votante = new Hash_votantes([
                    'id_votante' => $informacionUsuario->id,
                    'tipo' => 'votante',
                    'subtipo' => $request->input('comuna.value'),
                    'id_evento' => 15,
                    'candidato' => 0,
                    'validaciones' => $validacion,
                    'estado' => 'Pendiente',
                    'intentos' => 1,
                ]);
                $informacionUsuario->hashVotantes()->save($hash_votante);
            }

            // Asignar roles al usuario
            $user->syncRoles('Usuarios');

            // Confirmar la transacción
            DB::commit();
            Cache::forget('votantes');
            // Eliminar el código de verificación
            // $verification->delete();


            return back();
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();
            // Eliminar el código de verificación
            // $verification->delete();

            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al crear el usuario: ' . $e->getMessage()]);
        }
    }

    public function usuariosDuplicados($id_user) {
        // Busca el usuario por el id recibido
    $usuario = User::with('votantes', 'biometrico')
        ->find($id_user);

    if (!$usuario) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }

    return response()->json(['usuario' => $usuario]);
    }

    public function verificar(Request $request)
    {
        $request->validate([
            'identificacion' => 'required'
        ]);

        $anio_actual = Carbon::now()->year;

        $existe = Informacion_votantes::where('identificacion', $request->identificacion)
            ->where('comuna', '!=', 0)
            ->whereNotNull('comuna')
            ->whereHas('user')
            ->exists();

        $votante = [];
        if ($request->registro_presencial) {

            if (!$existe) {
                $existe = Informacion_votantes::where('identificacion', $request->identificacion)
                    ->where('comuna', '!=', 0)
                    ->whereNotNull('comuna')
                    ->exists();
            }

            $votante = Informacion_votantes::select('id', 'identificacion', 'nombre', 'id_user')
                ->where('identificacion', $request->identificacion)
                ->where('comuna', '!=', 0)
                ->whereNotNull('comuna')
                ->with([
                    'votos' => function ($query) use ($anio_actual) {
                        $query->whereYear('created_at', $anio_actual);
                    },
                    'hashVotantes'
                ])
                ->first();
        } else if (!$existe) {

            $votante = Informacion_votantes::where('identificacion', $request->identificacion)
                ->where('comuna', '!=', 0)
                ->whereNotNull('comuna')
                ->with([
                    'votos' => function ($query) use ($anio_actual) {
                        $query->whereYear('created_at', $anio_actual);
                    },
                    'hashVotantes:id,id_votante,subtipo'
                ])
                ->first();
        }

        return response()->json(['existe' => $existe, 'votante' => $votante]);
    }

    //editar registro corrección
    public function edit(Request $request, $id_votante)
    {

        $votante =  Informacion_votantes::with('user.biometrico')
            ->with([
                'hashVotantes' => function ($query) {
                    $query->where('subtipo', '!=', 0);
                }
            ])
            ->findOrFail($id_votante);

        return Inertia::render(
            'CorregirDatos',
            [
                'votante' => $votante,
            ]
        );
    }

    public function corregirDatos(Request $request)
    {

        $request->validate([
            'nombre' => 'required|string|max:255',
            'identificacion' => 'required|string|max:20|unique:votantes,identificacion,' . $request->id_votante . ',id',
            'tipo_documento' => 'required|string',

            'nacimiento' => 'required|date',
            'genero' => 'required|string',
            'etnia' => 'required|string',
            'condicion' => 'required|string',
            'comuna' => 'required',
            'barrio' => 'required',
            'direccion' => 'string',


        ]);



        $votante = Informacion_votantes::findOrFail($request->id_votante);
        $user = User::where('id', $votante->id_user)->first();
        $biometrico = UsuariosBiometricos::where('user_id', $user->id)->first();
        $hash_votante = Hash_votantes::where('id_votante', $votante->id)->first();

        // Crear el usuario
        try {

            $fileNameFront = 'NA';
            // $fileNameBack = 'NA';
            $fileNamePhoto = 'NA';
            $folderDoc = 'documentos';
            $folderPhoto = 'fotos';
            // Obtener la extensión original de los archivos
            if ($request->hasFile('cedula_front')) {

                $request->validate([
                    'cedula_front' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                ]);

                $frontExtension = $request->file('cedula_front')->getClientOriginalExtension();

                $fileNameFront = 'cedula_front_' . $request->identificacion . '.' . $frontExtension;

                // Guardar los archivos con su extensión original
                $frontPath = $request->file('cedula_front')->storeAs('uploads/' . $folderDoc, $fileNameFront, 'public');
            }

            // if ($request->hasFile('cedula_back')) {

            //     $backExtension = $request->file('cedula_back')->getClientOriginalExtension();

            //     $fileNameBack = 'cedula_back_' . $request->identificacion . '.' . $backExtension;

            //     // Guardar los archivos con su extensión original
            //     $backPath = $request->file('cedula_back')->storeAs('uploads/' . $folderDoc, $fileNameBack, 'public');
            // }

            if ($request->hasFile('photo')) {

                //foto evidencia

                $fileNamePhoto = 'foto' . $request->identificacion . '.' . $request->file('photo')->getClientOriginalExtension();

                $fotoPath = $request->file('photo')->storeAs('uploads/' . $folderPhoto, $fileNamePhoto, 'public');
            }


            $validacion = $request->validaciones;

            if ($request->has('embedding')) {
                $nuevoEmbedding = is_string($request->embedding)
                    ? $request->embedding
                    : json_encode($request->embedding);

                $embeddingActual = $biometrico->embedding;

                // Compara los JSON decodificados para evitar diferencias de formato
                $nuevoArray = json_decode($nuevoEmbedding, true);
                $actualArray = json_decode($embeddingActual, true);

                if ($nuevoArray !== $actualArray) {
                    $biometrico->embedding = $nuevoEmbedding; // Actualiza el embedding solo si es diferente
                }
            }

            if ($request->embedding && ($validacion == "" && $validacion == 'posible robot o spam')) {

                //ACTUALIZAR REGISTRO BIOMETRICO

                $biometrico->photo = $fileNamePhoto != 'NA' ? $fileNamePhoto : $biometrico->photo;
                $biometrico->cedula_front = $fileNameFront != 'NA' ? $fileNameFront : $biometrico->cedula_front;
                // $biometrico->cedula_back = $fileNameBack != 'NA' ? $fileNameBack : $biometrico->cedula_back;
                $biometrico->estado = 'Validado';
                $biometrico->motivo = 'Validación exitosa';



                if ($validacion != 'posible robot o spam') {

                    $validacion = 'validado';
                }
            } else {


                //ACTUALIZAR REGISTRO BIOMETRICO no validado

                $biometrico->photo = $fileNamePhoto != 'NA' ? $fileNamePhoto : $biometrico->photo;
                $biometrico->cedula_front = $fileNameFront != 'NA' ? $fileNameFront : $biometrico->cedula_front;
                // $biometrico->cedula_back = $fileNameBack != 'NA' ? $fileNameBack : $biometrico->cedula_back;
                // $biometrico->firma = $firma != 'NA' ? $firma : $biometrico->firma;
                $biometrico->estado = 'Pendiente';
                $biometrico->motivo = $validacion;
            }


            $biometrico->save();

            $user->identificacion = $request->identificacion;
            $user->save();

            // Actualizar la información del votante asociada al usuario
            $votante->nombre = $request->nombre;
            $votante->identificacion = $request->identificacion;
            $votante->tipo_documento = $request->tipo_documento;

            $votante->nacimiento = $request->nacimiento;
            $votante->genero = $request->genero;
            $votante->etnia = $request->etnia;
            $votante->condicion = $request->condicion;
            $votante->comuna = $request->input('comuna.value');
            $votante->barrio = $request->barrio;
            $votante->direccion = $request->direccion;
            $votante->save();

            // Actualizar el registro de hash_votantes asociado a la información del votante
            $hash_votante->subtipo = $request->input('comuna.value');
            $hash_votante->validaciones = $validacion;
            $hash_votante->estado = 'Pendiente';
            $hash_votante->intentos += 1;
            $hash_votante->save();




            // Confirmar la transacción
            DB::commit();
            Cache::forget('votantes');


            return redirect()->back();
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();

            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al actualizar el usuario: ' . $e->getMessage()]);
        }


        return redirect()->back();
    }
}
