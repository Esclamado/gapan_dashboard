<?php
namespace Lns\Gpn\Controller\WebApi\Customers\Action;

class Delete extends \Lns\Sb\Controller\Controller {

    protected $_users;
    protected $_userprofile;
    protected $_address;
    protected $_contact;
    protected $_cart;
    protected $_orders;

    protected $token;
    protected $payload;
    
    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session,
        \Lns\Sb\Lib\Token\Validate $Validate,
        \Lns\Sb\Lib\Entity\Db\Users $Users,
        \Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
        \Lns\Sb\Lib\Entity\Db\Address $Address,
        \Lns\Sb\Lib\Entity\Db\Contact $Contact,
        \Lns\Gpn\Lib\Entity\Db\Cart $Cart,
        \Lns\Gpn\Lib\Entity\Db\Orders $Orders
    ) {
        parent::__construct($Url,$Message,$Session);
        $this->token = $Validate;
        $this->_users = $Users;
        $this->_userprofile = $UserProfile;
        $this->_address = $Address;
        $this->_contact = $Contact;
        $this->_cart = $Cart;
        $this->_orders = $Orders;
    }
    public function run() {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->validate($this->_request, true);

        $this->jsonData['error'] = 1;

        if($payload['error'] == 1){
            $this->jsonData['message'] = $payload['message'];
        } else {
            $userId = $payload['payload']['jti'];

            $user_id = $this->getParam('id');

            $user = $this->_users->getByColumn(['id'=>$user_id], 1);
            $profile = $this->_userprofile->getByColumn(['user_id' => $user_id], 1);
            $address = $this->_address->getByColumn(['profile_id' => $user_id], 1);
            $contact = $this->_contact->getByColumn(['profile_id' => $user_id], 1);
            if($user){
                $cart = $this->_cart->getByColumn(['user_id'=>$user_id], 1);
                $orders = $this->_orders->getByColumn(['user_id'=>$user_id], 1);

                if($cart || $orders){
                    $this->jsonData['message'] = 'User can not be deleted';
                }else{
                    $user->delete();
                    if($profile){
                        $profile->delete();
                    }
                    if($address){
                        $address->delete();
                    }
                    if($contact){
                        $contact->delete();
                    }
                    $this->jsonData['message'] = 'User has been deleted';
                    $this->jsonData['error'] = 0;
                }
            }else{
                $this->jsonData['message'] = 'No user record found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>