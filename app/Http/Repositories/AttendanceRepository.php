<?php

namespace App\Http\Repositories;

interface AttendanceRepository
{
    function findAll();
    function findOne($id);
    function findByUser($user);
    function findByUserAndMonth($user, $month);
    function findByUserPosition($position);
    function findByUserDepartment($department);
    function save($attendance);
    function deleteById($id);
    function deleteByUser($user);
}
