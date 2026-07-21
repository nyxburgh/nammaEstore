<?php
namespace App\Admin\Services;
use App\Core\Database;
use App\Repositories\UserRepository;

class SellerService
{
    private UserRepository $users;
    private Database  $db;
    public function __construct() { $this->users=new UserRepository(); $this->db=Database::getInstance(); }

    public function create(array $d, int $byAdmin): array {
        if ($this->users->findByEmail($d['email'])) return ['success'=>false,'message'=>'Email already registered.'];
        $this->db->beginTransaction();
        try {
            $uid = $this->users->insert(['name'=>$d['name'],'email'=>$d['email'],'phone'=>$d['phone']??null,'password'=>password_hash($d['password'],PASSWORD_DEFAULT),'role'=>'seller','is_active'=>1,'is_verified'=>1,'created_by'=>$byAdmin]);
            $this->db->insert("INSERT INTO `".DB_PREFIX."seller_profiles` (user_id,shop_name,shop_slug,description,gst_number,pan_number,status,approved_by,approved_at) VALUES(?,?,?,?,?,?,'active',?,NOW())", [$uid,$d['shop_name'],slugify($d['shop_name']).'-'.$uid,$d['description']??null,$d['gst_number']??null,$d['pan_number']??null,$byAdmin]);
            $free=$this->db->fetchOne("SELECT id FROM `".DB_PREFIX."subscription_plans` WHERE slug='free' LIMIT 1");
            if ($free) $this->db->insert("INSERT INTO `".DB_PREFIX."seller_subscriptions` (seller_id,plan_id,status,started_at,amount_paid) VALUES(?,?,'active',NOW(),0)", [$uid,$free['id']]);
            $this->db->commit();
            (new ActivityService())->log('admin',$byAdmin,'create_seller','sellers',"Created seller: {$d['name']}");
            return ['success'=>true,'seller_id'=>$uid];
        } catch (\Exception $e) { $this->db->rollBack(); return ['success'=>false,'message'=>$e->getMessage()]; }
    }
    public function approve(int $uid, int $byAdmin): array { $this->users->updateSellerStatus($uid,'active',$byAdmin); (new ActivityService())->log('admin',$byAdmin,'approve_seller','sellers',"Approved #$uid"); return ['success'=>true,'message'=>'Seller approved.']; }

    public function reject(int $uid, int $byAdmin, string $note): array { $this->users->updateSellerStatus($uid,'rejected',$byAdmin,$note); (new ActivityService())->log('admin',$byAdmin,'reject_seller','sellers',"Rejected #$uid"); return ['success'=>true,'message'=>'Seller rejected.']; }
    public function suspend(int $uid, int $byAdmin): array { $this->users->updateSellerStatus($uid,'suspended',$byAdmin); $this->users->update($uid,['is_active'=>0]); (new ActivityService())->log('admin',$byAdmin,'suspend_seller','sellers',"Suspended #$uid"); return ['success'=>true,'message'=>'Seller suspended.']; }

    public function update(int $uid, array $d, int $byAdmin): array {
        if (empty(trim($d['name'] ?? ''))) return ['success' => false, 'message' => 'Name is required.'];
        if (empty(trim($d['shop_name'] ?? ''))) return ['success' => false, 'message' => 'Shop name is required.'];

        $existing = $this->users->findByEmail(trim($d['email'] ?? ''));
        if ($existing && (int) $existing['id'] !== $uid) {
            return ['success' => false, 'message' => 'Email already in use by another account.'];
        }

        $this->db->beginTransaction();
        try {
            $userUpdate = [
                'name'  => $d['name'],
                'email' => $d['email'],
                'phone' => $d['phone'] ?? null,
            ];
            if (!empty($d['password'])) {
                if (strlen($d['password']) < 8) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
                }
                $userUpdate['password'] = password_hash($d['password'], PASSWORD_DEFAULT);
            }
            $this->users->update($uid, $userUpdate);
            $this->db->execute(
                "UPDATE `".DB_PREFIX."seller_profiles` SET
                    shop_name=?, description=?, gst_number=?, pan_number=?, seller_type=?,
                    shop_phone=?, address=?, city=?, state=?, pincode=?
                 WHERE user_id=?",
                [
                    $d['shop_name'], $d['description'] ?? null, $d['gst_number'] ?? null, $d['pan_number'] ?? null,
                    in_array($d['seller_type'] ?? '', ['subscription','commission'], true) ? $d['seller_type'] : 'commission',
                    $d['shop_phone'] ?? null, $d['address'] ?? null, $d['city'] ?? null,
                    $d['state'] ?? null, $d['pincode'] ?? null,
                    $uid,
                ]
            );
            $this->db->commit();
            (new ActivityService())->log('admin', $byAdmin, 'update_seller', 'sellers', "Updated seller #{$uid}");
            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function getEarnings(int $sellerId): array {
        return $this->db->fetchOne("SELECT COALESCE(SUM(seller_earning),0) as total_earning,COALESCE(SUM(commission_amount),0) as total_commission,COALESCE(SUM(gross_amount),0) as gross_sales,COUNT(DISTINCT order_id) as total_orders,COALESCE(SUM(CASE WHEN payout_status='pending' THEN seller_earning ELSE 0 END),0) as pending_payout FROM `".DB_PREFIX."order_seller_splits` WHERE seller_id=?", [$sellerId]) ?? [];
    }
    public function options(): array { return $this->users->getAllSellers(); }

    public function list(int $page, array $filters): array { return $this->users->getSellers($page, $filters); }

    public function find(int $id): ?array { return $this->users->getSellerProfile($id); }

    public function toggleStatus(int $id): void { $this->users->toggleStatus($id); }
}
