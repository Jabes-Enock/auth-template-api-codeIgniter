<?php

namespace App\Controllers\Api\Auth;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Entities\User;

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

            $user = $this->removeWhiteSpaces();

            $rules = [
                "username" => 'required|is_unique[users.username]|max_length[30]|min_length[3]',
                "email" => 'required|valid_email|is_unique[auth_identities.secret]',
                "password" => 'required|min_length[5]|max_length[15]',
            ];

            if (!$this->validateData($user, $rules)) {
                return $this->respond($this->validator->getErrors());
            }

            $userEntity = new User($user);

            if (!$this->model->save($userEntity)) {
                return $this->respond($this->model->errors());
            }

            return $this->respondCreated();

        } catch (\Exception $e) {

            return $this->failServerError();
        }
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

            $user = $this->removeWhiteSpaces();

            $rules = [
                "email" => 'required|valid_email|',
                "password" => 'required',
            ];

            if (!$this->validateData($user, $rules)) {
                return $this->respond($this->validator->getErrors());
            }

            $attempt = auth()->attempt($user);

            if (!$attempt->isOK()) {
                return $this->respond(["message" => "user not found"]);
            }

            $user = $this->getUserOr404(auth()->id());

            $tokenData = $user->generateAccessToken("token");

            $token = $tokenData->raw_token;

            return $this->respondCreated(["token" => $token]);

        } catch (\Exception $e) {
            return $this->failServerError();
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
            return $this->failServerError();
        }
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return ResponseInterface
     */
    public function logout()
    {
        auth()->logout();

        auth()->user()->revokeAllAccessTokens();

        return $this->respondCreated();
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return ResponseInterface
     */
    public function setEmail($id)
    {
        try {

            $data = $this->removeWhiteSpaces();

            $rules = [
                "email" => 'required|valid_email',
                "confirm_email" => 'required|matches[email]'
            ];

            if (!$this->validateData($data, $rules)) {
                return $this->respond($this->validator->getErrors());
            }
            // Get the User Provider (UserModel by default)
            $users = auth()->getProvider();

            $user = $users->findById(auth()->id());

            if ($user->email === $data['email']) {
                return $this->failValidationError();
            }

            $user->fill([
                'email' => $data['email'],
            ]);

            if ($this->model->save($user)) {
                return $this->respondCreated();
            }

            return $this->respond(["message" => "Something goes wrong."]);

        } catch (\Exception $e) {
            return $this->failServerError();
        }
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return ResponseInterface
     */
    public function setUsername($id)
    {
        try {

            $data = $this->removeWhiteSpaces();

            $rules = [
                "username" => "required|is_unique[users.username]|max_length[30]|min_length[3]",
                "confirm_username" => 'required|matches[username]'
            ];

            if (!$this->validateData($data, $rules)) {
                return $this->respond($this->validator->getErrors());
            }

            // Get the User Provider (UserModel by default)
            $users = auth()->getProvider();

            $user = $users->findById(auth()->id());

            if ($user->username === $data['username']) {
                return $this->failValidationError();
            }

            $user->fill([
                'username' => $data['username'],
            ]);

            if (!$this->model->save($user)) {
                return $this->respond($this->model->errors());
            }

            return $this->respondCreated();

        } catch (\Exception $e) {
            return $this->failServerError();
        }
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        try {
            $user = $this->getUserOr404($id);
            // Get the User Provider (UserModel by default)
            $users = auth()->getProvider();
            /*
            soft delete
            https://shield.codeigniter.com/user_management/managing_users/#deleting-users
            */
            $users->delete($user->id);

            //delete all of the user's data from the system
            //$users->delete($user->id, true); 

            return $this->respondDeleted();

        } catch (\Exception $e) {
            return $this->failServerError("something goes wrong");
        }
    }



    /**
     * Delete the designated resource object from the model
     *
     * @return ResponseInterface
     */
    public function accessDenied()
    {
        return $this->failUnauthorized();
    }

    public function getUserOr404($id)
    {
        //use CodeIgniter\Shield\Models\UserModel;
        $users = auth()->getProvider();

        $user = $users->findById($id);

        if (!$user) {
            return $this->failNotFound();
        }

        return $user;
    }

    public function removeWhiteSpaces(): array
    {
        $data = $this->request->getJson(true);

        $cleanData = array_map(function ($item) {
            return trim(preg_replace("/\s+/", " ", $item));
        }, $data);

        return $cleanData;
    }

}
