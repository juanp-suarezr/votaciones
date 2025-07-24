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
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'identificacion' => 'required|string|max:20',
            Rule::unique('informacion_votantes')->where(function ($query) {
                return $query->whereNotNull('comuna')->where('comuna', '!=', 0);
            }),
            'tipo_documento' => 'required|string',
            'fecha_expedicion' => 'required|date',
            'lugar_expedicion' => 'required|string',
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
            'indicativo' => 'required|string|max:5',
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
                    $request->indicativo . $request->celular, // Número de destino
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'codigo' => 'required|integer',
            'checked' => 'required',
            'declaracion' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar el código
        $verification = VerificationCode::where('email', $request->email)
            ->where('code', $request->codigo)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return back()->withErrors(['error' => 'Código de verificación inválido o expirado.']);
        }


        // Crear el usuario
        try {

            // Obtener la extensión original de los archivos
            $frontExtension = $request->file('cedula_front')->getClientOriginalExtension();
            $backExtension = $request->file('cedula_back')->getClientOriginalExtension();


            $folderDoc = 'documentos';
            $fileNameFront = 'cedula_front_' . $request->identificacion . '.' . $frontExtension;
            $fileNameBack = 'cedula_back_' . $request->identificacion . '.' . $backExtension;
            // Guardar los archivos con su extensión original
            $frontPath = $request->file('cedula_front')->storeAs('uploads/' . $folderDoc, $fileNameFront, 'public');
            $backPath = $request->file('cedula_back')->storeAs('uploads/' . $folderDoc, $fileNameBack, 'public');

            //foto evidencia
            $folderPhoto = 'fotos';
            $fileNamePhoto = 'foto' . $request->identificacion . '.' . $request->file('photo')->getClientOriginalExtension();

            $fotoPath = $request->file('photo')->storeAs('uploads/' . $folderPhoto, $fileNamePhoto, 'public');

            //gestion firma
            $firma = 'NA';
            if ($request->hasFile('firma')) {
                $folder = 'firmas';
                $original = $request->file('firma');
                $extension = strtolower($original->getClientOriginalExtension());
                $firma = time() . '_votante_' . $request->identificacion . '.' . $extension;

                $rutaDestino = storage_path('app/public/uploads/' . $folder . '/' . $firma);

                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $img = imagecreatefromjpeg($original->getPathname());
                    imagejpeg($img, $rutaDestino, 60); // 70 es la calidad, puedes bajarla más si quieres
                    imagedestroy($img);
                } elseif ($extension === 'png') {
                    $img = imagecreatefrompng($original->getPathname());
                    imagepng($img, $rutaDestino, 7); // 0 (sin compresión) a 9 (máxima compresión)
                    imagedestroy($img);
                } else {
                    // Otros formatos, solo mover
                    $original->move(storage_path('app/public/uploads/' . $folder), $firma);
                }
            }

            $validacion = $request->validaciones;
            // Log::info('embedding', ['embedding' => json_encode($request->embedding)]);

            // Crear el usuario
            $user = User::create([
                'name' => $request->nombre,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'estado' => 'Activo',

            ]);

            if ($request->embedding && ($validacion == "" || $validacion == 'posible robot o spam')) {

                //CREAR REGISTRO BIOMETRICO
                $registroBiometrico = new UsuariosBiometricos([
                    'user_id' => $user->id,
                    'embedding' => json_encode($request->embedding), // convertir a string JSON
                    'photo' => $fileNamePhoto,
                    'cedula_front' => $fileNameFront,
                    'cedula_back' => $fileNameBack,
                    'firma' => $firma,
                    'estado' => 'Validado',

                ]);

                if ($validacion != 'posible robot o spam') {

                    $validacion = 'validado';
                }
            } else {

                //CREAR REGISTRO BIOMETRICO no validado
                $registroBiometrico = new UsuariosBiometricos([
                    'user_id' => $user->id,
                    'embedding' => json_encode($request->embedding), // convertir a string JSON
                    'photo' => $fileNamePhoto,
                    'cedula_front' => $fileNameFront,
                    'cedula_back' => $fileNameBack,
                    'firma' => $firma,
                    'estado' => 'Pendiente',

                ]);
            }


            $user->biometrico()->save($registroBiometrico);

            // Crear la información del votante asociada al usuario
            $informacionUsuario = new Informacion_votantes([
                'nombre' => $request->nombre,
                'id_user' => $user->id,
                'identificacion' => $request->identificacion,
                'tipo_documento' => $request->tipo_documento,
                'fecha_expedicion' => $request->fecha_expedicion,
                'lugar_expedicion' => $request->lugar_expedicion,
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
            ]);
            $informacionUsuario->hashVotantes()->save($hash_votante);

            // Asignar roles al usuario
            $user->syncRoles('Usuarios');

            // Confirmar la transacción
            DB::commit();
            Cache::forget('votantes');
            // Eliminar el código de verificación
            $verification->delete();


            return back();
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();
            // Eliminar el código de verificación
            $verification->delete();

            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al crear el usuario: ' . $e->getMessage()]);
        }
    }

    public function verificar(Request $request)
    {
        $request->validate([
            'identificacion' => 'required'
        ]);

        $existe = Informacion_votantes::where('identificacion', $request->identificacion)
            ->where('comuna', '!=', 0)
            ->whereNotNull('comuna')
            ->exists();

        if($existe && $request->registro_presencial) {
            $votante = Informacion_votantes::select('id', 'identificacion', 'id_user')
                ->where('identificacion', $request->identificacion)
                ->where('comuna', '!=', 0)
                ->whereNotNull('comuna')
                ->with('votos')
                ->with('hashVotantes')
                ->first();
            return response()->json(['existe' => $existe, 'votante' => $votante]);
        }

        return response()->json(['existe' => $existe]);
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
            'fecha_expedicion' => 'required|date',
            'lugar_expedicion' => 'required|string',
            'nacimiento' => 'required|date',
            'genero' => 'required|string',
            'etnia' => 'required|string',
            'condicion' => 'required|string',
            'comuna' => 'required',
            'barrio' => 'string',
            'direccion' => 'string',

        ]);

        $votante = Informacion_votantes::findOrFail($request->id_votante);
        $user = User::where('id', $votante->id_user)->first();
        $biometrico = UsuariosBiometricos::where('user_id', $user->id)->first();
        $hash_votante = Hash_votantes::where('id_votante', $votante->id)->first();

        // Crear el usuario
        try {

            $fileNameFront = 'NA';
            $fileNameBack = 'NA';
            $fileNamePhoto = 'NA';
            // Obtener la extensión original de los archivos
            if ($request->hasFile('cedula_front') && $request->hasFile('cedula_back')) {
                $frontExtension = $request->file('cedula_front')->getClientOriginalExtension();
                $backExtension = $request->file('cedula_back')->getClientOriginalExtension();


                $folderDoc = 'documentos';
                $fileNameFront = 'cedula_front_' . $request->identificacion . '.' . $frontExtension;
                $fileNameBack = 'cedula_back_' . $request->identificacion . '.' . $backExtension;
                // Guardar los archivos con su extensión original
                $frontPath = $request->file('cedula_front')->storeAs('uploads/' . $folderDoc, $fileNameFront, 'public');
                $backPath = $request->file('cedula_back')->storeAs('uploads/' . $folderDoc, $fileNameBack, 'public');

                //foto evidencia
                $folderPhoto = 'fotos';
                $fileNamePhoto = 'foto' . $request->identificacion . '.' . $request->file('photo')->getClientOriginalExtension();

                $fotoPath = $request->file('photo')->storeAs('uploads/' . $folderPhoto, $fileNamePhoto, 'public');
            }


            //gestion firma
            $firma = 'NA';
            if ($request->hasFile('firma')) {
                $folder = 'firmas';
                $original = $request->file('firma');
                $extension = strtolower($original->getClientOriginalExtension());
                $firma = time() . '_votante_' . $request->identificacion . '.' . $extension;

                $rutaDestino = storage_path('app/public/uploads/' . $folder . '/' . $firma);

                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $img = imagecreatefromjpeg($original->getPathname());
                    imagejpeg($img, $rutaDestino, 60); // 70 es la calidad, puedes bajarla más si quieres
                    imagedestroy($img);
                } elseif ($extension === 'png') {
                    $img = imagecreatefrompng($original->getPathname());
                    imagepng($img, $rutaDestino, 7); // 0 (sin compresión) a 9 (máxima compresión)
                    imagedestroy($img);
                } else {
                    // Otros formatos, solo mover
                    $original->move(storage_path('app/public/uploads/' . $folder), $firma);
                }
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

                $biometrico->photo = $fileNamePhoto;
                $biometrico->cedula_front = $fileNameFront != 'NA' ? $fileNameFront : $biometrico->cedula_front;
                $biometrico->cedula_back = $fileNameBack != 'NA' ? $fileNameBack : $biometrico->cedula_back;
                $biometrico->firma = $firma != 'NA' ? $firma : $biometrico->firma;
                $biometrico->estado = 'Validado';


                if ($validacion != 'posible robot o spam') {

                    $validacion = 'validado';
                }
            } else {


                //ACTUALIZAR REGISTRO BIOMETRICO no validado

                $biometrico->photo = $fileNamePhoto;
                $biometrico->cedula_front = $fileNameFront != 'NA' ? $fileNameFront : $biometrico->cedula_front;
                $biometrico->cedula_back = $fileNameBack != 'NA' ? $fileNameBack : $biometrico->cedula_back;
                $biometrico->firma = $firma != 'NA' ? $firma : $biometrico->firma;
                $biometrico->estado = 'Pendiente';


            }


            $biometrico->save();

            // Actualizar la información del votante asociada al usuario
            $votante->nombre = $request->nombre;
            $votante->identificacion = $request->identificacion;
            $votante->tipo_documento = $request->tipo_documento;
            $votante->fecha_expedicion = $request->fecha_expedicion;
            $votante->lugar_expedicion = $request->lugar_expedicion;
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
