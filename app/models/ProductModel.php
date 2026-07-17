<?php
namespace App\Models;

use App\Core\Model;

/**
 * Plain data entity for `products`.
 *
 * Query-shaping logic moved to App\Repositories\ProductRepository.
 * This class only keeps generic CRUD inherited from Model
 * (findById, insert, update, delete, count).
 *
 * Do not add SQL-heavy methods back here — new query logic goes in
 * ProductRepository, and Services call the Repository, not this Model.
 */
class ProductModel extends Model
{
    protected string $table = 'products';
}
