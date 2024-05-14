<?php

namespace App\Http\Repositories;

interface UserRepository
{
    function findAll();
    function findOne($id);
    function findByEmail($email);
    function save($user);
    function deleteById($id);
}
