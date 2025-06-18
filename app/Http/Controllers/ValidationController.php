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
            'email' => 'required|email|unique:users',
            'celular' => 'required|string|max:15',
            'indicativo' => 'required|string|max:5',
            'password' => 'required|string|min:8',
            'recaptcha_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verifica el token de reCAPTCHA
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => env('RECAPTCHA_SECRET_KEY'), // Llave secreta de reCAPTCHA
                'response' => $request->recaptcha_token,
            ]);

            $recaptchaData = $response->json();

            Log::info('reCAPTCHA response:', $recaptchaData);

            if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
                return response()->json(['errors' => 'La validación de reCAPTCHA falló. Inténtelo nuevamente.'], 422);
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
        Log::info('Código de verificación generado: ' . $verificationCode);
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

        return response()->json(['message' => 'Código de verificación enviado.']);
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

        // Eliminar el código de verificación
        $verification->delete();

        // Crear el usuario
        try {
            // Crear el usuario
            $user = User::create([
                'name' => $request->nombre,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'estado' => 'Activo',
            ]);

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

            $validacion = $request->validaciones;
            Log::info('embedding', ['embedding' => json_encode($request->embedding)]);

            if ($request->embedding && $validacion == "") {

                //CREAR REGISTRO BIOMETRICO
                $registroBiometrico = new UsuariosBiometricos([
                    'user_id' => $user->id,
                    'embedding' => json_encode($request->embedding), // convertir a string JSON
                    'photo' => $fileNamePhoto,
                    'cedula_front' => $fileNameFront,
                    'cedula_back' => $fileNameBack,
                    'estado' => 'Validado',

                ]);

                $validacion = 'validado';
            } else {

                //CREAR REGISTRO BIOMETRICO no validado
                $registroBiometrico = new UsuariosBiometricos([
                    'user_id' => $user->id,
                    'embedding' => json_encode($request->embedding), // convertir a string JSON
                    'photo' => $fileNamePhoto,
                    'cedula_front' => $fileNameFront,
                    'cedula_back' => $fileNameBack,
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

            return back();
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();

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

        return response()->json(['existe' => $existe]);
    }
}
