<?php
namespace App\Frontend\Controllers;
use App\Core\{Auth, Middleware};
use App\Frontend\Services\{AccountService, CartService, SettingsService, ReviewService, WishlistService};

class AccountController extends FrontendController
{
    private function base(): array {
        return ['cartCount'=>(new CartService())->getCount(),'settings'=>SettingsService::all(),'categories'=>(new \App\Frontend\Services\ProductService())->getCategories(),'user'=>Auth::user()];
    }
    public function overview(): void {
        Middleware::userAuth();
        $this->view('account.overview', array_merge($this->base(),['title'=>'My Account','stats'=>(new AccountService())->getStats(Auth::userId()),'recentOrders'=>(new AccountService())->getOrders(Auth::userId(),1)]));
    }
    public function orders(): void {
        Middleware::userAuth();
        $status = $this->input('status','');
        $this->view('account.orders', array_merge($this->base(),['title'=>'My Orders','orders'=>(new AccountService())->getOrders(Auth::userId(),(int)$this->input('page',1),$status),'status'=>$status]));
    }
    public function orderDetail(string $id): void {
        Middleware::userAuth();
        $order = (new AccountService())->getOrderDetail((int)$id, Auth::userId());
        if (!$order) { $this->redirect(APP_URL.'/account/orders'); return; }
        $this->view('account.order-detail', array_merge($this->base(),['title'=>'Order #'.e($order['order_number']),'order'=>$order]));
    }
    public function requestReturn(): void {
        csrf_check();
        Middleware::userAuth();
        $r = (new \App\Frontend\Services\CustomerReturnService())->submit(
            Auth::userId(),
            (int) $this->input('order_id'),
            (int) $this->input('order_item_id'),
            $this->input('type', 'return'),
            $this->input('reason', ''),
            $this->input('note', '')
        );
        $this->setFlash($r['success'] ? 'success' : 'error', $r['message']);
        $this->redirect(APP_URL . '/account/orders/' . $this->input('order_id'));
    }
    public function returns(): void {
        Middleware::userAuth();
        $this->view('account.returns', array_merge($this->base(), [
            'title'   => 'My Returns',
            'returns' => (new \App\Frontend\Services\CustomerReturnService())->myReturns(Auth::userId(), (int) $this->input('page', 1)),
        ]));
    }
    public function wishlist(): void {
        Middleware::userAuth();
        $this->view('account.wishlist', array_merge($this->base(),['title'=>'My Wishlist','items'=>(new WishlistService())->getByUser(Auth::userId(),(int)$this->input('page',1))]));
    }
    public function addresses(): void {
        Middleware::userAuth();
        $this->view('account.addresses', array_merge($this->base(),['title'=>'My Addresses','addresses'=>(new AccountService())->getAddresses(Auth::userId())]));
    }
    public function addAddress(): void {
        csrf_check();
        Middleware::userAuth();
        (new AccountService())->addAddress(Auth::userId(), $_POST);
        $this->setFlash('success','Address saved.'); $this->redirect(APP_URL.'/account/addresses');
    }
    public function deleteAddress(string $id): void {
        csrf_check();
        Middleware::userAuth();
        (new AccountService())->deleteAddress((int)$id, Auth::userId());
        $this->json(['success'=>true]);
    }
    public function setDefaultAddress(string $id): void {
        csrf_check();
        Middleware::userAuth();
        (new AccountService())->setDefault((int)$id, Auth::userId());
        $this->json(['success'=>true]);
    }
    public function notifications(): void {
        Middleware::userAuth();
        $svc = new \App\Core\Services\NotificationService();
        $this->view('account.notifications', array_merge($this->base(),[
            'title'         => 'Notifications',
            'notifications' => $svc->getForUser('customer', Auth::userId(), (int) $this->input('page', 1)),
        ]));
    }
    public function markNotificationRead(string $id): void {
        csrf_check();
        Middleware::userAuth();
        (new \App\Core\Services\NotificationService())->markRead((int) $id, 'customer', Auth::userId());
        $this->json(['success' => true]);
    }
    public function reviews(): void {
        Middleware::userAuth();
        $this->view('account.reviews', array_merge($this->base(),['title'=>'My Reviews','reviews'=>(new ReviewService())->getByUser(Auth::userId(),(int)$this->input('page',1))]));
    }
    public function profile(): void {
        Middleware::userAuth();
        $this->view('account.profile', array_merge($this->base(),['title'=>'Account Settings']));
    }
    public function updateProfile(): void {
        csrf_check();
        Middleware::userAuth();
        $r = (new AccountService())->updateProfile(Auth::userId(), $_POST);
        $this->setFlash($r['success']?'success':'error', $r['success']?'Profile updated.':$r['message']);
        if (!$r['success']) { $this->redirectWithInput(APP_URL.'/account/profile'); return; }
        $this->redirect(APP_URL.'/account/profile');
    }
    public function updatePassword(): void {
        csrf_check();
        Middleware::userAuth();
        // Email OTP proves mailbox control on top of the current
        // password — request one via sendPasswordOtp() first.
        $otpCheck = (new \App\Core\Services\OtpService())->verify(
            Auth::user()['email'], 'password_change', $this->input('otp', '')
        );
        if (!$otpCheck['success']) {
            $this->setFlash('error', $otpCheck['message']);
            $this->redirect(APP_URL.'/account/profile');
            return;
        }
        if ($this->input('new_password') !== $this->input('confirm_password')) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->redirect(APP_URL.'/account/profile');
            return;
        }
        $r = (new AccountService())->updatePassword(Auth::userId(), $this->input('current_password',''), $this->input('new_password',''));
        $this->setFlash($r['success']?'success':'error', $r['message']??'Password updated.');
        $this->redirect(APP_URL.'/account/profile');
    }

    public function sendPasswordOtp(): void {
        csrf_check();
        Middleware::userAuth();
        $r = (new \App\Core\Services\OtpService())->send(
            Auth::user()['email'], 'password_change',
            'Confirm your password change — Namma E Store',
            'Your password change confirmation code is'
        );
        $this->setFlash($r['success'] ? 'success' : 'error',
            $r['success'] ? 'A confirmation code has been sent to your email.' : $r['message']);
        $this->redirect(APP_URL.'/account/profile');
    }
    public function payments(): void {
        Middleware::userAuth();
        $txns = (new AccountService())->getTransactions(Auth::userId());
        $this->view('account.payments', array_merge($this->base(),['title'=>'Payments','transactions'=>$txns]));
    }
    public function wallet(): void {
        Middleware::userAuth();
        $svc = new \App\Frontend\Services\CustomerWalletService();
        $this->view('account.wallet', array_merge($this->base(), [
            'title'        => 'My Wallet',
            'wallet'       => $svc->overview(Auth::userId()),
            'transactions' => $svc->transactions(Auth::userId(), (int) $this->input('page', 1)),
        ]));
    }
    public function loyalty(): void {
        Middleware::userAuth();
        $svc = new \App\Core\Services\LoyaltyService();
        $this->view('account.loyalty', array_merge($this->base(), [
            'title'        => 'Loyalty Points',
            'loyalty'      => $svc->overview(Auth::userId()),
            'transactions' => $svc->transactions(Auth::userId(), (int) $this->input('page', 1)),
            'rupeeValue'   => $svc->pointsToRupees($svc->balance(Auth::userId())),
            'minRedeem'    => $svc->minRedeem(),
        ]));
    }
}
