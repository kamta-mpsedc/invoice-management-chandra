<?php
namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class Auth extends ResourceController
{
    // REGISTER
    public function register()
    {
        helper('form');
        $model = new UserModel();

        $rules = [
            'name'     => 'required',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[Admin,User]',
        ];

        if (! $this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        // $data = [
        //     'name'     => $this->request->getPost('name'),
        //     'email'    => $this->request->getPost('email'),
        //     'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        //     'role'     => $this->request->getPost('role'),
        // ];

        $data             = $this->request->getJSON(true); // IMPORTANT
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $model->save($data);

        return $this->respond([
            'status'  => 1,
            'message' => 'User Registered Successfully',
        ]);

    }
    public function login()
    {
        return view('login');
    }

    // LOGIN
    public function authlogin()
    {

        $model = new UserModel();

        $data     = $this->request->getJSON(true); // IMPORTANT
        $email    = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $rules    = [

            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',

        ];

        if (! $this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $user = $model->where('email', $email)->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return $this->failUnauthorized('Invalid Credentials');
        }

        $token = bin2hex(random_bytes(32));
        $model->update($user['id'], [
            'token' => $token,
        ]);

        $session = \Config\Services::session();

        $session->set([
            'id'         => $user['id'],
            'name'       => $user['name'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'token'      => $token,
            'isLoggedIn' => true,
        ]);

        //  return redirect()->to(uri: 'dashboard');

        return $this->respond([
            'status'  => 1,
            'message' => 'Login Successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ]);
        return redirect()->back()->with('error', 'Invalid credentials');
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

}
