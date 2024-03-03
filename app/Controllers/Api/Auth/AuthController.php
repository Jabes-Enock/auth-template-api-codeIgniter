<?php

namespace App\Controllers\Api\Auth;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class AuthController extends ResourceController
{
    protected $modelName = 'CodeIgniter\Shield\Models\UserModel';
    protected $format = 'json';
    /**
     * 
     *  
     * 
     * Return an array of resource objects, themselves in array format
     *
     * @return ResponseInterface
     */
    public function register()
    {
        try {
            $rules = [
                "username" => [
                    "label" => "username",
                    "rules" => "required|is_unique[users.username]"
                ],
                "email" => [
                    "label" => "email",
                    "rules" => "required|valid_email|is_unique[auth_identities.secret]"
                ],
                "password" => [
                    "label" => "password",
                    "rules" => "required|min_length[5]|max_length[15]"
                ],
            ];

            if (!$this->validate($rules)) {
                return $this->respond($this->validator->getErrors());
            }

            $user = $this->request->getJSON();

            $userEntity = new User([
                "username" => $user->username,
                "email" => $user->email,
                "password" => $user->password,
            ]);

            if ($this->model->save($userEntity)) {
                return $this->respondCreated();
            }

            return $this->respond(["message" => "Something goes wrong."]);

        } catch (\Exception $e) {

            return $this->failServerError("Não foi possivel se conectar ao servidor.");
        }

        /*  $data = ["route" => "register"];

         return $this->respond($data); */
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return ResponseInterface
     */

    public function login()
    {
        try {

            if (auth()->loggedIn()) {
                auth()->logout();
            }

            $rules = [
                "email" => [
                    "label" => "email",
                    "rules" => "required|valid_email"
                ],
                "password" => [
                    "label" => "password",
                    "rules" => "required"
                ]
            ];

            if (!$this->validate($rules)) {
                return $this->respond($this->validator->getErrors());
            }

            $user = $this->request->getJSON();

            $credentials = [
                "email" => $user->email,
                "password" => $user->password,
            ];

            $attempt = auth()->attempt($credentials);

            if (!$attempt->isOK()) {
                return $this->respond(["message" => 'user not found']);
            }
            $model = new UserModel();

            $user = $model->findById(auth()->id());

            $tokenData = $user->generateAccessToken('token');

            $token = $tokenData->raw_token;

            return $this->respondCreated(['token' => $token]);

        } catch (\Exception $e) {
            return $this->failServerError("Não foi possivel se conectar ao servidor.");
        }
    }


    /**
     * Return the properties of a resource object
     *
     * @return ResponseInterface
     */
    public function profile()
    {
        try {
            $id = auth()->id();

            $user = $this->model
                ->select("users.id, users.username, auth_identities.secret as email")
                ->join("auth_identities", "users.id = auth_identities.user_id")
                ->findById($id);

            return $this->respond($user);

        } catch (\Exception $e) {
            return $this->failServerError("Não foi possivel se conectar ao servidor.");
        }
    }

    public function logout()
    {
        auth()->logout();
        auth()->user()->revokeAllAccessTokens();

        return $this->respondCreated();
    }



    /**
     * Return the editable properties of a resource object
     *
     * @return ResponseInterface
     */

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return ResponseInterface
     */
    public function accessDenied($id = null)
    {
        return $this->failUnauthorized();
    }

}
