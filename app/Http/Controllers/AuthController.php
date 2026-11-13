<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

// login, cadastro, logout e recuperacao de senha
class AuthController extends Controller
{
    // REGISTRO
    public function register(Request $request)
    {
        $request->merge([
            'cpf' => preg_replace('/\D/', '', $request->cpf)
        ]);

        $request->validate([
    'name' => 'required|min:3',

    'cpf' => [
        'required',
        'digits:11',
        'unique:users',
        'regex:/^\d{11}$/'
    ],

    'email' => 'required|email|unique:users',

    'data_nascimento' => 'required',

    'password' => 'required|min:8|confirmed',

    'tipo' => 'required|in:aluno,professor'
], [

    // CPF
    'cpf.required' => 'O CPF é obrigatório.',
    'cpf.unique' => 'Este CPF já está cadastrado.',
    'cpf.digits' => 'O CPF deve ter 11 dígitos.',
    'cpf.regex' => 'Digite um CPF válido.',

    // EMAIL
    'email.required' => 'O email é obrigatório.',
    'email.email' => 'Digite um email válido.',
    'email.unique' => 'Este email já está cadastrado.',

    // SENHA
    'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
    'password.confirmed' => 'As senhas não coincidem.'
]);

        $firstAdmin = !User::where('is_admin', true)->exists();
        $isProfessor = $request->tipo === 'professor';

        // CRIA USUÁRIO
        $user = User::create([
            'name' => $request->name,
            'cpf' => $request->cpf,
            'email' => $request->email,
            'data_nascimento' => $request->data_nascimento,
            'tipo' => $request->tipo,
            'is_admin' => $firstAdmin,
            'approval_status' => $isProfessor && !$firstAdmin ? 'pending' : 'approved',
            'approval_requested_at' => $isProfessor && !$firstAdmin ? now() : null,
            'password' => Hash::make($request->password),
            'foto' => 'images/default-user.png',
        ]);

        if ($isProfessor && !$firstAdmin) {
            return redirect()->route('login')->with('status', 'Cadastro enviado para análise. Você poderá acessar o sistema após aprovação de um administrador.');
        }

        // LOGIN AUTOMÁTICO
        Auth::login($user);

        // REGENERA SESSÃO
        $request->session()->regenerate();

        // REDIRECIONA PRA HOME
        return redirect('/home');
    }

    // LOGIN
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->tipo === 'professor' && $user->approval_status === 'pending') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->view('auth.professor-pending', ['email' => $credentials['email']]);
            }

            if ($user->tipo === 'professor' && $user->approval_status === 'rejected') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only('email'))->withErrors([
                    'email' => 'Seu cadastro de professor foi negado. Entre em contato com a administração.',
                ]);
            }

            $request->session()->regenerate();

            return redirect('/home');
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'E-mail ou senha inválidos. Verifique os dados e tente novamente.',
        ]);
    }

    // recuperacao de senha por codigo no email
    public function forgotPasswordForm()
    {
        return view('auth.esqueci-senha', [
            'step' => session('password_reset_verified') ? 'reset' : (session('password_reset_email') ? 'code' : 'email'),
            'email' => session('password_reset_email'),
        ]);
    }

    public function sendPasswordResetCode(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'Não encontramos uma conta com este e-mail.',
        ]);

        $code = (string) random_int(100000, 999999);

        session([
            'password_reset_email' => $data['email'],
            'password_reset_code_hash' => Hash::make($code),
            'password_reset_expires_at' => now()->addMinutes(15)->timestamp,
            'password_reset_verified' => false,
        ]);

        Mail::raw("Seu código de recuperação UniceHub é: {$code}\n\nEle expira em 15 minutos.", function ($message) use ($data) {
            $message->to($data['email'])->subject('Código de recuperação de senha - UniceHub');
        });

        return redirect()->route('esqueci-senha')->with('status', 'Enviamos um código de 6 dígitos para o seu e-mail.');
    }

    public function verifyPasswordResetCode(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|digits:6',
        ]);

        if (!session('password_reset_email') || !session('password_reset_code_hash')) {
            return redirect()->route('esqueci-senha')->withErrors(['email' => 'Solicite um novo código para continuar.']);
        }

        if (now()->timestamp > (int) session('password_reset_expires_at')) {
            session()->forget(['password_reset_code_hash', 'password_reset_expires_at', 'password_reset_verified']);
            return redirect()->route('esqueci-senha')->withErrors(['code' => 'O código expirou. Solicite um novo código.']);
        }

        if (!Hash::check($data['code'], session('password_reset_code_hash'))) {
            return back()->withErrors(['code' => 'Código inválido. Confira o e-mail e tente novamente.']);
        }

        session(['password_reset_verified' => true]);

        return redirect()->route('esqueci-senha')->with('status', 'Código validado. Agora defina sua nova senha.');
    }

    public function resetPasswordWithCode(Request $request)
    {
        if (!session('password_reset_verified') || !session('password_reset_email')) {
            return redirect()->route('esqueci-senha')->withErrors(['email' => 'Valide o código antes de trocar a senha.']);
        }

        $data = $request->validate([
            'password' => 'required|min:8|confirmed',
        ], [
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'As senhas não coincidem.',
        ]);

        $user = User::where('email', session('password_reset_email'))->firstOrFail();
        $user->password = Hash::make($data['password']);
        $user->save();

        session()->forget(['password_reset_email', 'password_reset_code_hash', 'password_reset_expires_at', 'password_reset_verified']);

        return redirect()->route('login')->with('status', 'Senha redefinida com sucesso. Faça login com a nova senha.');
    }

    // LOGOUT
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
