<?php
namespace App\Models;

use App\Core\Model;

/**
 * Plain data entity for `carts`.
 *
 * Query-shaping logic moved to App\Repositories\CartRepository.
 * This class only keeps generic CRUD inherited from Model
 * (findById, insert, update, delete, count).
 *
 * Do not add SQL-heavy methods back here — new query logic goes in
 * CartRepository, and Services call the Repository, not this Model.
 */
class CartModel extends Model
{
    protected string $table = 'carts';
}
