<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;
use Config\Database;

class Users extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function index()
    {

        $data['users'] = $this->userModel->findAll();
        echo view('users/index', $data);
    }

    public function create()
    {
        echo view('users/create');
    }

    public function store()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'name'     => 'required|max_length[50]',
            'username' => 'required|max_length[50]',
            'code'     => 'required|max_length[20]',
            'email'    => 'required|valid_email|max_length[191]',
            'password' => 'required|min_length[6]',
            'status'   => 'required|max_length[191]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'name'     => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'code'     => $this->request->getPost('code'),
            'uuid'     => uuid_create(UUID_TYPE_RANDOM),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'status'   => $this->request->getPost('status'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->userModel->save($data);
        return redirect()->to('/users');
    }

    public function edit($id = null)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('User not found');
        }
        echo view('users/edit', ['user' => $user]);
    }

    public function update($id = null)
    {
        $validation = \Config\Services::validation();

        $rules = [
            'name'     => 'required|max_length[50]',
            'username' => 'required|max_length[50]',
            'code'     => 'required|max_length[20]',
            'email'    => 'required|valid_email|max_length[191]',
            'status'   => 'required|max_length[191]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'name'       => $this->request->getPost('name'),
            'username'   => $this->request->getPost('username'),
            'code'       => $this->request->getPost('code'),
            'email'      => $this->request->getPost('email'),
            'status'     => $this->request->getPost('status'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Update password only if provided
        $password = $this->request->getPost('password');
        if ($password) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);
        return redirect()->to('/users');
    }

    public function delete($id = null)
    {
        $this->userModel->delete($id);
        return redirect()->to('/users');
    }
}
