<?php
namespace App\Models;

use App\Core\Model;

/**
 * Plain data entity for `mc_orders`.
 *
 * NOTE: query-shaping logic (filters, joins, stats, monthly revenue,
 * status-change transactions) now lives in App\Repositories\OrderRepository.
 * This class only keeps the generic CRUD inherited from Model
 * (findById, insert, update, delete, count) — nothing order-specific.
 *
 * Do not add SQL-heavy methods back here. New query logic goes in
 * OrderRepository, and Services call the Repository, not this Model.
 */
class OrderModel extends Model
{
    protected string $table = 'orders';
}
