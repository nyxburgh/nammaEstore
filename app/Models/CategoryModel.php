<?php
namespace App\Models;

use App\Core\Model;

/**
 * Plain data entity for `categories`.
 *
 * Query-shaping logic moved to App\Repositories\CategoryRepository.
 * This class only keeps generic CRUD inherited from Model
 * (findById, insert, update, delete, count).
 *
 * Do not add SQL-heavy methods back here — new query logic goes in
 * CategoryRepository, and Services call the Repository, not this Model.
 */
class CategoryModel extends Model
{
    protected string $table = 'categories';
}
