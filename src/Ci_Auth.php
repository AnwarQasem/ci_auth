<?php

namespace Anwarqasem\CiAuth;

require 'vendor/autoload.php';

use Anwarqasem\CiAuth\Models\UsersModel;
use Anwarqasem\CiAuth\Models\TokenModel;
use Config\Services;
use Exception;
use Firebase\JWT\JWT;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use ReflectionException;

/**
 *
 */
class Ci_Auth implements FilterInterface
{

    /**
     * @var UsersModel
     */
    private UsersModel $usersModel;
    /**
     * @var TokenModel
     */
    private TokenModel $tokenModel;

    /**
     *
     */
    function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->tokenModel = new TokenModel();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function login(): array
    {
        $validation = Services::validation();
        $request    = Services::request();
        $jsonArray  = $request->getJSON(true);

        $validation->setRules([
            "email"    => 'required|valid_email',
            "password" => 'required|min_length[8]'
        ], [
            "email"    => [
                'required'    => "Email is required!",
                'valid_email' => "Email address is not in format!",
            ],
            "password" => [
                'required'   => "Password is required!",
                'min_length' => "Password minimum 8 characters"
            ]
        ]);

        if (!$validation->run($jsonArray)) {
            return ['error' => true, 'data' => $validation->getErrors()];
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

                $result = ['error' => false, 'data' => $return_user];
            } else {
                $result = ['error' => true, 'data' => ["Login failed. Could not login."]];
            }
        }

        return $result;
    }

    /**
     * @throws ReflectionException
     */
    public function register(): array
    {
        $validation = Services::validation();
        $request    = Services::request();

        $jsonArray = $request->getJSON(true);

        $validation->setRules([
            "name"     => 'required|min_length[4]',
            "email"    => 'required|valid_email',
            "password" => 'required|min_length[8]'
        ], [
            "name"     => [
                'required'   => "Name is required!",
                'min_length' => "Name minimum 4 characters"
            ],
            "email"    => [
                'required'    => "Email is required!",
                'valid_email' => "Email address is not in format!",
            ],
            "password" => [
                'required'   => "Password is required!",
                'min_length' => "Password minimum 8 characters"
            ]
        ]);

        if (!$validation->run($jsonArray)) {
            return ['error' => true, 'data' => $validation->getErrors()];
        }

        $user = $this->usersModel->insert($jsonArray);

        if ($user === false) {
            $result = ['error' => true, 'data' => $this->usersModel->errors()];
        } else {
            $result = ['error' => false, 'data' => $this->usersModel->find($user)];
        }

        return $result;

    }

    /**
     * @throws ReflectionException
     */
    public function forgot_password(): array
    {
        $validation = Services::validation();
        $request    = Services::request();
        $email      = Services::email();
        $jsonArray  = $request->getJSON(true);

        $validation->setRules([
            "email" => 'required|valid_email'
        ], [
            "email" => [
                'required'    => "Email is required!",
                'valid_email' => "Email address is not in format!",
            ]
        ]);

        if (!$validation->run($jsonArray)) {
            return ['error' => true, 'data' => $validation->getErrors()];
        }

        $key_unique = md5(password_hash(date("U") . rand(1000, 9999), PASSWORD_DEFAULT));
        $key_date   = date("U");
        $key        = $key_unique . " - " . $key_date;

        $user = $this->usersModel->where('email', $jsonArray['email'])->first();
        if (!$user) {
            return ['error' => true, 'data' => ['Email not found']];
        }
        $sql = $this->usersModel->update($user->id, ['password_recovery' => $key]);
        if (!$sql) {
            return ['error' => true, 'data' => ['Password recovery failed.']];
        }

        $change_password_link = getenv('FRONTEND_URL') . "/recover/$user->email/$key_unique";

        $email->setFrom('office@muravian.com', 'MURAVIAN TEST');
        $email->setTo('anwar.subhi@gmail.com');

        $email->setSubject('Password recovery');
        $email->setMessage("Please click on the following link: <a href='$change_password_link'>$change_password_link</a>");

        if ($email->send()) {
            return ['error' => false, 'data' => ['An email was send with the reset password link']];
        } else {
            return ['error' => true, 'data' => ['Password recovery failed.']];
        }

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
                'required'   => "Password is required!",
                'min_length' => "Password minimum 8 characters"
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
            $key     = getenv('JWT_SECRET');
            $decoded = JWT::decode($authorization, $key, array('HS256'));
        } catch (Exception $e) { // Also tried JwtException
            return false;
        }

        $token = $this->tokenModel->where('token', $authorization)->first();

        if(isset($token) && $decoded->exp > date("U")) {
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
        $exp = $iat + 3600;

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

    /**
     * @param RequestInterface $request
     * @param null $arguments
     * @return bool
     */
    public function before(RequestInterface $request, $arguments = null): bool
    {
        if($this->isLogged()) {
            return true;
        }
        return false;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}