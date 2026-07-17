<?php
namespace App\Models;

use App\Core\Model;

/**
 * Plain data entity for `wishlists`.
 *
 * Query-shaping logic moved to App\Repositories\WishlistRepository.
 * This class only keeps generic CRUD inherited from Model
 * (findById, insert, update, delete, count).
 *
 * Do not add SQL-heavy methods back here — new query logic goes in
 * WishlistRepository, and Services call the Repository, not this Model.
 */
class WishlistModel extends Model
{
    protected string $table = 'wishlists';
}
