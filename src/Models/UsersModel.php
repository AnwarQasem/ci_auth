<?php namespace Anwarqasem\CiAuth\Models;

use CodeIgniter\Model;


class UsersModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'object';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['name', 'email', 'password', 'password_recovery'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $beforeInsert = ['encryptPassword'];
    protected $beforeUpdate = ['encryptPassword'];

    protected $afterFind = ['removePassword'];

    public function login($data)
    {
        $sql = $this->allowCallbacks(false)->where('email', $data['email'])->first();

        if ($sql && password_verify($data['password'], $sql->password)) {
            return $sql;
        } else {
            return $this->errors();
        }


    }

    public function encryptPassword($data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    public function removePassword($data)
    {
        unset($data['data']->password);
        unset($data['data']->deleted_at);

        return $data;
    }

}
