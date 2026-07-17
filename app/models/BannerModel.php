<?php
namespace App\Models;

use App\Core\Model;

/**
 * Plain data entity for `banners`.
 *
 * Query-shaping logic moved to App\Repositories\BannerRepository.
 * This class only keeps generic CRUD inherited from Model
 * (findById, insert, update, delete, count).
 *
 * Do not add SQL-heavy methods back here — new query logic goes in
 * BannerRepository, and Services call the Repository, not this Model.
 */
class BannerModel extends Model
{
    protected string $table = 'banners';
}
