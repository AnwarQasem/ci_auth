<?php

namespace Anwarqasem\CiAuth;

use Anwarqasem\CiAuth\Models\UsersModel;
use Anwarqasem\CiAuth\Models\TokenModel;
use Config\Services;
use Exception;
use Firebase\JWT\JWT;
use ReflectionException;


/**
 *
 */
class CiAuth
{


    /**
     * @var UsersModel
     */
    private $usersModel;

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     *
     */
    public function initController()
    {
        $this->usersModel = new UsersModel();
        $this->tokenModel = new TokenModel();
    }

    /**
     * @throws ReflectionException
     */
    public function login()
    {
        $validation = Services::validation();
        $request    = Services::request();
        $jsonArray  = $request->getJSON(true);

        $validation->setRules([
            "email"    => 'required|valid_email',
            "password" => 'required|min_length[8]'
        ], [
            "email"    => [
                'required'    => lang('validation.email_required'),
                'valid_email' => lang('validation.email_valid_email')
            ],
            "password" => [
                'required'   => lang('validation.password_required'),
                'min_length' => lang('validation.password_min_length')
            ]
        ]);

        if (!$validation->run($jsonArray)) {
            $this->view(['error' => true, 'data' => $validation->getErrors()]);
        }

        $user = $this->usersModel->login($jsonArray);

        if ($user === false) {
            $result = ['error' => true, 'data' => $this->usersModel->errors()];
        } else if (is_array($user) && count($user) == 0) {
            $result = ['error' => true, 'data' => ["Login failed. Email or password incorrect"]];
        } else {

            $token = $this->token($user);
            if ($token) {
                $return_user        = $this->usersModel->select(['id', 'name', 'email'])->find($user->id);
                $return_user->token = $token;
                $result             = ['error' => false, 'data' => $return_user];
            } else {
                $result = ['error' => true, 'data' => ["Login failed. Could not login."]];
            }
        }

        $this->view($result);
    }

    /**
     * @throws ReflectionException
     */
    public function register()
    {
        $validation = Services::validation();
        $request    = Services::request();

        $jsonArray = $request->getJSON(true);

        $validation->setRules([
            "name"     => 'required|min_length[4]',
            "email"    => 'required|valid_email|is_unique[users.email]',
            "password" => 'required|min_length[8]'
        ], [
            "name"     => [
                'required'   => lang('validation.name_required'),
                'min_length' => lang('validation.name_min_length')
            ],
            "email"    => [
                'required'    => lang('validation.email_required'),
                'valid_email' => lang('validation.email_valid_email'),
                'is_unique'   => lang('validation.email_is_unique')
            ],
            "password" => [
                'required'   => lang('validation.password_required'),
                'min_length' => lang('validation.password_min_length')
            ]
        ]);

        if (!$validation->run($jsonArray)) {
            $this->view(['error' => true, 'data' => $validation->getErrors()]);
        }

        $user = $this->usersModel->insert($jsonArray);

        if ($user === false) {
            $result = ['error' => true, 'data' => $this->usersModel->errors()];
        } else {
            $result = ['error' => false, 'data' => $this->usersModel->find($user)];
        }

        $this->view($result);

    }

    /**
     * @throws ReflectionException
     */
    public function forgot_password()
    {
        $validation = Services::validation();
        $request    = Services::request();
        $email      = Services::email();
        $jsonArray  = $request->getJSON(true);

        $validation->setRules([
            "email" => 'required|valid_email'
        ], [
            "email" => [
                'required'    => lang('validation.email_required'),
                'valid_email' => lang('validation.email_valid_email'),
            ]
        ]);

        if (!$validation->run($jsonArray)) {
            $this->view(['error' => true, 'data' => $validation->getErrors()]);
        }

        $key_unique = md5(password_hash(date("U") . rand(1000, 9999), PASSWORD_DEFAULT));
        $key_date   = date("U");
        $key        = $key_unique . " - " . $key_date;

        $user = $this->usersModel->where('email', $jsonArray['email'])->first();
        if (!$user) {
            $result = ['error' => true, 'data' => ['Email not found']];
        }
        $sql = $this->usersModel->update($user->id, ['password_recovery' => $key]);
        if (!$sql) {
            $result = ['error' => true, 'data' => ['Password recovery failed.']];
        }

        $change_password_link = getenv('FRONTEND_URL') . "/recover/$user->email/$key_unique";

        $email->setFrom('office@muravian.com', 'MURAVIAN TEST');
        $email->setTo('anwar.subhi@gmail.com');

        $email->setSubject('Password recovery');
        $email->setMessage("Please click on the following link: <a href='$change_password_link'>$change_password_link</a>");

        if ($email->send()) {
            $result = ['error' => false, 'data' => ['An email was send with the reset password link']];
        } else {
            $result = ['error' => true, 'data' => ['Password recovery failed.']];
        }

        $this->view($result);
    }

    /**
     * @throws ReflectionException
     */
    public function change_password($email, $key): array
    {
        $validation = Services::validation();
        $request    = Services::request();
        $jsonArray  = $request->getJSON(true);

        $validation->setRules([
            "password" => 'required|min_length[8]'
        ], [
            "password" => [
                'required'   => lang('validation.password_required'),
                'min_length' => lang('validation.password_min_length')
            ]
        ]);

        if (!$validation->run($jsonArray)) {
            return ['error' => true, 'data' => $validation->getErrors()];
        }

        $user = $this->usersModel->where('email', $email)->first();
        if ($user && $user->password_recovery) {
            $recover      = explode(' - ', $user->password_recovery);
            $recover_time = date("U") - $recover[1];
            if ($recover[0] == $key && (int)$recover_time < 86400) {
                $data                      = $jsonArray;
                $data['password_recovery'] = null;
                $update                    = $this->usersModel->update($user->id, $data);
                if ($update) {
                    $result = ['error' => false, 'data' => ['Password changed successfully.']];
                } else {
                    $result = ['error' => true, 'data' => $this->usersModel->errors()];
                }
            } else {
                $result = ['error' => true, 'data' => ['Email or code mismatch']];
            }
        } else {
            $result = ['error' => true, 'data' => ['Email or code mismatch']];
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isLogged(): bool
    {
        try {
            $authorization = explode(" ", getallheaders()['Authorization'])[1];
            $key           = getenv('JWT_SECRET');
            $decoded       = JWT::decode($authorization, $key, array('HS256'));
        } catch (Exception $e) { // Also tried JwtException
            return false;
        }

        $token = $this->tokenModel->where('token', $authorization)->first();

        if (isset($token) && $decoded->exp > date("U")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws ReflectionException
     */
    private function token($user)
    {
        $key = getenv('JWT_SECRET');
        $iat = time();
        $tte = (int)getenv('JWT_IAT');
        $exp = $iat + ($tte * 3600);

        $payload = array(
            "iss"   => "Issuer of the " . getenv('JWT_ISS'),
            "aud"   => "Audience that the " . getenv('JWT_AUD'),
            "sub"   => "Subject of the " . getenv('JWT_SUB'),
            "iat"   => $iat, //Time the JWT issued at
            "exp"   => $exp, // Expiration time of token
            "email" => $user->email,
        );

        $token = JWT::encode($payload, $key);

        $data   = [
            'user_id' => $user->id,
            'token'   => $token
        ];
        $result = $this->tokenModel->insert($data);

        if ($result) {
            return $token;
        } else {
            return false;
        }
    }

    public function getProfile()
    {

    }

    public function updateProfile()
    {

    }

    /**
     * @param $data
     */
    public function view($data)
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Accept: application/json; charset=UTF-8');
        echo json_encode($data, JSON_PRETTY_PRINT);
        die();
    }
}