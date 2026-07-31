<?php
namespace App\Models;

use App\Core\Model;

/**
 * Plain data entity for `reviews`.
 *
 * Query-shaping logic moved to App\Repositories\ReviewRepository.
 * This class only keeps generic CRUD inherited from Model
 * (findById, insert, update, delete, count).
 *
 * Do not add SQL-heavy methods back here — new query logic goes in
 * ReviewRepository, and Services call the Repository, not this Model.
 */
class ReviewModel extends Model
{
    protected string $table = 'reviews';
}
