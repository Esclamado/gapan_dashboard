<?php

namespace Lns\Gpn\Controller\WebApi\Auth;

class Login extends \Lns\Sb\Controller\Controller
{

    protected $token;
    protected $payload;
    protected $destinationPath = 'images/profile';

    protected $attempts = 0;

    public function __construct(
        \Of\Http\Url $Url,
        \Of\Std\Message $Message,
        \Lns\Sb\Lib\Session\Session $Session
    ) {
        parent::__construct($Url, $Message, $Session);
        $this->_global = $this->_di->get('Lns\Sb\Controller\Api\AllFunction');
        $this->token = $this->_di->get('Lns\Sb\Lib\Token\Validate');
        $this->_userModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Users');
        $this->_userprofileModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\UserProfile');
        $this->_attachmentsModel = $this->_di->get('Lns\Sb\Lib\Entity\Db\Attachments');
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
            $email = $this->getParam('email');
            $requestPassword = $this->getParam('password');
            $action = $this->getParam('action');
            $talentId = $this->getParam('talent_id');
            $userExist = null;

            if ($email) {
                $checkemail = $this->_userModel->getByColumn(['email' => $email], 1);
                $checkuname = $this->_userModel->getByColumn(['username' => $email], 1);
                if($checkemail){
                    $userExist = $checkemail;
                }else if ($checkuname) {
                    $userExist = $checkuname;
                }
                if ($userExist) {
                    if ($userExist->getData('status') == 1) {
                        $passwordVerify = $this->Password->setPassword($requestPassword)
                            ->setHash($userExist->getData('password'))->verify();
                        if ($passwordVerify) {
                            $userData = $userExist->getData();
                            $userProfile = $this->_userprofileModel->getByColumn(['user_id' => $userExist->getData('id')]);
                            $userProfileData = $userProfile->getData();

                            if (isset($userData['password'])) {
                                unset($userData['password']);
                            }
                            /* $this->resetAttempt($userExist); */
                            $this->jsonData['error'] = 0;
                            $this->jsonData['message'] = $this->_lang->getLang('success');
                            $this->jsonData['data'] = $userData;
                            $this->jsonData['data']['user_profile'] = $userProfileData;
                            if ($userProfileData['profile_pic']) {
                                $this->jsonData['data']['user_profile']['profile_picture'] = $this->getImageUrl([
                                    'vendor' => 'Lns',
                                    'module' => 'Sb',
                                    'path' => '/images/uploads/profilepic/' . $userData['id'],
                                    'filename' => $userProfileData['profile_pic']
                                ]);
                            } else {
                                $this->jsonData['data']['user_profile']['profile_picture'] = null;
                            }

                            /* update last login */
                            $userExist->setData('last_login', date("Y-m-d H:i:s"));
                            $userExist->__save();
                                /* update last login */
                            $jwt = $this->_di->get('Lns\Sb\Lib\Token\Jwt');
                            $jwt->setIssuer($this->_url->getDomain());
                            $jwt->setAudience($userExist->getData('email'));
                            $jwt->setId($userExist->getData('id'));
                            $jwt->setIssuedAt(time());
                            $jwt->setSubject('loged user token');
                            /*$jwt->setExpiration(time() + $this->_siteConfig->getData('site_api_logged_id_token_max_age'));*/
                            $jwt->setSecret($this->_siteConfig->getData('site_api_secret'));
                            $this->jsonData['token'] = $jwt->getToken();
                        } else {
                            /* $this->failedAttempt($userExist); */
                            $this->jsonData['message'] = $this->_lang->getLang('password_current_incorrect');
                        }
                    } else if ($userExist->getData('status') == 0) {
                        $this->jsonData['message'] = 'User account is not yet active.';
                    } else if ($userExist->getData('status') == 2) {
                        $this->jsonData['message'] = 'User account is locked.';
                    }
                } else {
                    $this->jsonData['message'] = 'User not found.';
                }
            } else {
                $this->jsonData['message'] = 'Email or Username is required';
            }
        }
        $this->jsonEncode($this->jsonData);
        die;
    }

    public function failedAttempt($user)
    {
        $this->attempts = (int) $user->getData('login_attempt');
        $this->attempts++;
        if ($this->attempts <= 6) {
            $this->_userModel->setDatas(['id' => $user->getData('id'), 'login_attempt' => $this->attempts])->__save();
        } else {
            $this->lockAccount($user);
        }
    }

    public function lockAccount($account)
    {
        $this->_userModel->setDatas(['id' => $account->getData('id'), 'status' => 2])->__save();
    }

    public function resetAttempt($account)
    {
        $this->_userModel->setDatas(['id' => $account->getData('id'), 'login_attempt' => 0])->__save();
    }
}
?>