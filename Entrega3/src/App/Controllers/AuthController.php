<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\User;

class AuthController extends Controller
{
    public ?string $modelName = User::class;

    /**
     * Procesa el registro por POST.
     * Campos esperados: name, email, username, password
     */
    public function register()
    {
        $request = $this->request;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $sessionUser = $this->model->registrar([
                'name'             => $request->get('name'),
                'email'            => $request->get('email'),
                'username'         => $request->get('username'),
                'password'         => $request->get('password'),
                'rol'              => $request->get('rol'),
                'apellido'         => $request->get('apellido'),
                'dni'              => $request->get('dni'),
                'fecha_nacimiento' => $request->get('fecha_nacimiento'),
            ]);

            $this->request->setSession('user', $sessionUser);
            $this->log->info("Registro exitoso", ['username' => $sessionUser['nombre_usuario']]);

            header('Location: /perfil?registro_exitoso=1');
            exit;
        } catch (\Exception $e) {
            header('Location: /?auth=login&error=' . $e->getMessage() . '&registro=true');
            exit;
        }
    }

    /**
     * Procesa el login por POST.
     * Campos esperados: user-login, pass-login
     */
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $returnTo = $this->obtenerRetornoSeguro($this->request->get('return_to'));
        $loginUrl = '/?auth=login' . ($returnTo ? '&return_to=' . rawurlencode($returnTo) : '');
        $username = trim($this->request->get('nombre_usuario') ?? '');
        $password = $this->request->get('contrasena') ?? '';

        // Validar que los campos no estén vacíos
        if (empty($username) || empty($password)) {
            header('Location: ' . $loginUrl . '&error=1');
            exit;
        }

        // Sanitizar
        $username = trim($username);

        // Buscar usuario en base de datos
        $usuario = $this->model->findByUsername($username);

        // Instanciar RateLimit
        $rateLimit = $this->loadModel(\Paw\App\Models\RateLimit::class);

        // Verificar bloqueo antes de validar la contraseña
        $minutosRestantes = $rateLimit->obtenerMinutosRestantesBloqueo($username);
        if ($minutosRestantes > 0) {
            $this->log->info("Login fallido: usuario bloqueado por rate limit", ['username' => $username, 'minutos' => $minutosRestantes]);
            header('Location: ' . $loginUrl . '&error=bloqueado&minutos=' . $minutosRestantes);
            exit;
        }

        if (!$usuario) {
            $this->log->info("Login fallido: usuario no encontrado", ['username' => $username]);
            $rateLimit->registrarIntentoFallido($username);
            
            // Re-verificar si este último intento lo bloqueó
            $minutosRestantes = $rateLimit->obtenerMinutosRestantesBloqueo($username);
            if ($minutosRestantes > 0) {
                header('Location: ' . $loginUrl . '&error=bloqueado&minutos=' . $minutosRestantes);
                exit;
            }

            header('Location: ' . $loginUrl . '&error=1');
            exit;
        }

        // Verificar contraseña con password_verify
        if (!password_verify($password, $usuario['contrasena'])) {
            $this->log->info("Login fallido: contraseña incorrecta", ['username' => $username]);
            $rateLimit->registrarIntentoFallido($username);

            // Re-verificar si este último intento lo bloqueó
            $minutosRestantes = $rateLimit->obtenerMinutosRestantesBloqueo($username);
            if ($minutosRestantes > 0) {
                header('Location: ' . $loginUrl . '&error=bloqueado&minutos=' . $minutosRestantes);
                exit;
            }

            header('Location: ' . $loginUrl . '&error=1');
            exit;
        }

        // Login exitoso: resetear intentos fallidos
        $rateLimit->resetearIntentos($username);

        // Guardar datos en sesión
        $sessionUser = [
            'id'             => $usuario['id'],
            'nombre_usuario' => $usuario['nombre_usuario'],
            'email'          => $usuario['email'],
            'foto_perfil'    => $usuario['foto_perfil'],
            'contacto'       => $usuario['contacto'] ?? null,
        ];

        // Detectar rol según qué tabla tiene registro vinculado
        $adoptante = $this->model->getAdoptante((int) $usuario['id']);
        $refugio   = $this->model->getRefugio((int) $usuario['id']);

        if ($refugio) {
            $sessionUser['rol']        = 'refugio';
            $sessionUser['refugio_id'] = $refugio['usuario_id'];
        } elseif ($adoptante) {
            $sessionUser['rol']          = 'adoptante';
            $sessionUser['adoptante_id'] = $adoptante['usuario_id'];
        } else {
            // Por defecto adoptante si no hay datos vinculados (p.ej. admin o incompleto)
            $sessionUser['rol']          = 'adoptante';
            $sessionUser['adoptante_id'] = null;
        }

        $this->request->setSession('user', $sessionUser);

        $this->log->info("Login exitoso", ['username' => $username, 'user_id' => $usuario['id']]);

        header('Location: ' . ($returnTo ?? '/perfil'));
        exit;
    }

    private function obtenerRetornoSeguro($returnTo): ?string
    {
        if (!is_string($returnTo) || !$returnTo || !str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//')) {
            return null;
        }

        return $returnTo;
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->request->destroySession();

        header('Location: /?auth=login');
        exit;
    }
}