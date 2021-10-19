<?php namespace Anwarqasem\CiAuth\Models;

use CodeIgniter\Model;


class Users_tokenModel extends Model
{
    protected $table      = 'users_token';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'object';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['id','user_id','token','available','created_at','updated_at','deleted_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

}
