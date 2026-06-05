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
        $request= $this->request;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        $name     = trim($request->get('name') ?? '');
        $email    = trim($request->get('email') ?? '');
        $username = trim($request->get('username') ?? '');
        $password = $request->get('password') ?? '';
        $rol = $request->get('rol') ?? '';

        // Validación básica
        if (!$name || !$email || !$username || !$password) {
            header('Location: /iniciar-sesion?error=campos');
            exit;
        }

        // Sanitizar
        $name     = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $email    = filter_var($email, FILTER_SANITIZE_EMAIL);
        $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

        $existente = $this->model->findByUsername($username);
        if ($existente) {
            header('Location: /iniciar-sesion?error=usuario_existente&registro=true');
            exit;
        }

        // Hash seguro
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Crear usuario

        $userId = $this->model->crearUsuario([
            'nombre_usuario' => $username,
            'email'          => $email,
            'contrasena'     => $passwordHash,
            'contacto'       => null,
        ]);
        $this->log->info("Usuario creado", ['userId' => $userId]); /// temporal

        // Crear refugio o adoptante según el rol
        if ($rol === 'refugio') {
            $this->model->crearRefugio([
                'usuario_id'         => $userId,
                'nombre_institucion' => $name,
                'cuit'               => null,
            ]);
            $this->log->info("Refugio creado", ['refugio_id' => $userId]); /// temporal

            $refugio = $this->model->getRefugio((int) $userId);

            $sessionUserRefugio = [
                'id'             => $userId,
                'nombre_usuario' => $username,
                'email'          => $email,
                'rol'            => 'refugio',
                'refugio_id'     => $refugio ? $refugio['usuario_id'] : null,
            ];
            
            $this->request->setSession('user', $sessionUserRefugio);

        } else {
            $this->model->crearAdoptante([
                'usuario_id' => $userId,
                'nombre'     => $name,
                'apellido'   => '',
            ]);
            $this->log->info("Adoptante creado", ['adoptante_id' => $userId]); /// temporal

            $adoptante = $this->model->getAdoptante((int) $userId);

            $sessionUser = [
                'id'             => $userId,
                'nombre_usuario' => $username,
                'email'          => $email,
                'rol'            => 'adoptante',
                'adoptante_id'   => $adoptante ? $adoptante['usuario_id'] : null,
                'foto_perfil'    => null,
                'contacto'       => null,
            ];
            
            $this->request->setSession('user', $sessionUser);
        }
        $this->log->info("Registro exitoso", ['username' => $username]);

        header('Location: /perfil?registro_exitoso=1');
        exit;
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

        $username = trim($this->request->get('nombre_usuario') ?? '');
        $password = $this->request->get('contrasena') ?? '';

        // Validar que los campos no estén vacíos
        if (empty($username) || empty($password)) {
            header('Location: /iniciar-sesion?error=1');
            exit;
        }

        // Sanitizar
        $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

        // Buscar usuario en base de datos
        $usuario = $this->model->findByUsername($username);

        if (!$usuario) {
            $this->log->info("Login fallido: usuario no encontrado", ['username' => $username]);
            header('Location: /iniciar-sesion?error=1');
            exit;
        }

        // Verificar contraseña con password_verify
        if (!password_verify($password, $usuario['contrasena'])) {
            $this->log->info("Login fallido: contraseña incorrecta", ['username' => $username]);
            header('Location: /iniciar-sesion?error=1');
            exit;
        }

        // Login exitoso: guardar datos en sesión
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

        header('Location: /perfil');
        exit;
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

        header('Location: /iniciar-sesion');
        exit;
    }
}