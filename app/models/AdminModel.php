<?php
namespace App\Models;

use App\Core\Model;

/**
 * Plain data entity for `admins`.
 *
 * Query-shaping logic moved to App\Repositories\AdminRepository.
 * This class only keeps generic CRUD inherited from Model
 * (findById, insert, update, delete, count).
 *
 * Do not add SQL-heavy methods back here — new query logic goes in
 * AdminRepository, and Services call the Repository, not this Model.
 */
class AdminModel extends Model
{
    protected string $table = 'admins';
}
