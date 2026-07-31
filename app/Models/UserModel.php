<?php
namespace App\Models;

use App\Core\Model;

/**
 * Plain data entity for `users`.
 *
 * Query-shaping logic moved to App\Repositories\UserRepository.
 * This class only keeps generic CRUD inherited from Model
 * (findById, insert, update, delete, count).
 *
 * Do not add SQL-heavy methods back here — new query logic goes in
 * UserRepository, and Services call the Repository, not this Model.
 */
class UserModel extends Model
{
    protected string $table = 'users';
}
