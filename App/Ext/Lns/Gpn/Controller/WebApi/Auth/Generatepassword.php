<?php

namespace Lns\Gpn\Controller\WebApi\Auth;

class Generatepassword extends \Lns\Sb\Controller\Controller
{

    protected $token;
    protected $payload;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ) {
        parent::__construct($Url, $Message, $Session);
        $this->_global = $this->_di->get('Lns\Sb\Controller\Api\AllFunction');
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
        $this->_userModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
        $this->Password = $this->_di->get('Lns\Sb\Lib\Password\Password');
    }

    public function run()
    {
        $payload = $this->token
            ->setLang($this->_lang)
            ->setSiteConfig($this->_siteConfig)
            ->setExpiration($this->_siteConfig->getData('site_api_token_max_time', 60 * 3), false)
            ->validate($this->_request);

        $this->jsonData['error'] = 1;

        if ($payload['error'] == 1) {
            $this->jsonData['message'] = $payload['message'];
        } else {
            $id = $this->getParam('id');
            
            $entity = $this->_userModel->getByColumn(['id' => $id], 1);

            if ($entity) {
                $role_id = $entity->getData('user_role_id');

                $password = $this->Password->generate(5);

                if ($role_id == 3) {
                    $password = "CST" . $password;
                } else if ($role_id == 4) {
                    $password = "MNG" . $password;
                } else if ($role_id == 5) {
                    $password = "SA" . $this->Password->generate(6);
                } else if ($role_id == 6) {
                    $password = "INS1" . $this->Password->generate(4);
                } else if ($role_id == 7) {
                    $password = "INS2" . $this->Password->generate(4);
                } else if ($role_id == 8) {
                    $password = "FLM" . $password;
                } else if ($role_id == 9) {
                    $password = "SRT" . $password;
                } else if ($role_id == 10) {
                    $password = "WHM1" . $this->Password->generate(4);
                } else if ($role_id == 11) {
                    $password = "WHM2" . $this->Password->generate(4);
                }

                $this->jsonData['data'] = $password;
                $this->jsonData['error'] = 0;
            } else {
                $this->jsonData['message'] = 'User not found';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }
}
?>