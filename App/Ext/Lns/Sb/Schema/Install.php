<?php
namespace Lns\Sb\Schema;

use Of\Std\Status;
use Of\Std\Password;
use Lns\Sb\Lib\Userrole;

class Install extends \Of\Db\Createtable {
	
	protected $_roles;
	protected $_users;
	protected $_userProfile;
	protected $_password;
	protected $_region;
	protected $_state;
	protected $_city;
	protected $_zipcode;
	protected $_address;
	protected $_contact;
	protected $_permissions;

	public function __construct(
		\Of\Std\Versioncompare $Versioncompare,
		\Lns\Sb\Lib\Entity\Db\Roles $Roles,
		\Lns\Sb\Lib\Entity\Db\Users $Users,
		\Lns\Sb\Lib\Entity\Db\UserProfile $UserProfile,
		\Lns\Sb\Lib\Entity\Db\Contact $Contact,
		\Lns\Sb\Lib\Entity\Db\Address $Address,
		\Lns\Sb\Lib\Entity\Db\Region $Region,
		\Lns\Sb\Lib\Entity\Db\State $State,
		\Lns\Sb\Lib\Entity\Db\City $City,
		\Lns\Sb\Lib\Entity\Db\Zipcode $Zipcode,
		\Lns\Sb\Lib\Entity\Db\Permissions $Permissions,
		Password $Password
	){
		parent::__construct($Versioncompare);
		$this->_roles = $Roles;
		$this->_users = $Users;
		$this->_address = $Address;
		$this->_userProfile = $UserProfile;
		$this->_contact = $Contact;
		$this->_password = $Password;
		$this->_region = $Region;
		$this->_state = $State;
		$this->_city = $City;
		$this->_zipcode = $Zipcode;
		$this->_permissions = $Permissions;
	}

	public function createSchema(){
		$this->createRolesTable();
		$this->createPermissionsTable();
		$this->createPermissionTable();
		$this->createUsersTable();
		$this->createUsersProfileTable();
		$this->createUsersAddressTable();
		$this->createUsersContactTable();
		$this->createDeviceTokenTable();
		$this->createHelpPagesTable();
		$this->createAttachmentsTable();
		$this->createEmailTemplateTable();
		$this->createUserSocialTable();
		$this->createNotificationTable();
		$this->createRegionTable();
		$this->createStateTable();
		$this->createCityTable();
		$this->createZipCodeTable();
		$this->createActivationCodeTable();
		$this->createCmsTable();
		$this->_region->installData();
		$this->_state->installData();
		$this->_city->installData();
		$this->_zipcode->installData();
	}

	protected function createUserSocialTable() {
		$this->setTablename('social');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'user_id',
			'type'=> self::_BIGINT,
			'length' => 20,
		]);
		$this->addColumn([
			'name'=>'type',
			'type'=> self::_VARCHAR,
			'length' => 50,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'social_id',
			'type'=> self::_BIGINT,
			'length' => 30,
			'nullable' => false,
			'comment' => 'social id',
		]);
		$this->addColumn([
			'name'=>'first_name',
			'type'=> self::_VARCHAR,
			'length' => 50,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'last_name',
			'type'=> self::_VARCHAR,
			'length' => 50,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'email',
			'type'=> self::_VARCHAR,
			'length' => 100,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'image_url',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'social_details',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'update_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}

	protected function createRolesTable() {
		$this->setTablename('roles');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 10,
			'ai' => self::_AI,
			'comment' => 'role id',
		]);
		$this->addColumn([
			'name'=>'name',
			'type'=> self::_VARCHAR,
			'length' => 60,
			'nullable' => false,
			'comment' => 'role title',
		]);
		$this->addColumn([
			'name'=>'description',
			'type'=> self::_TEXT,
			'nullable' => true,
			'comment' => 'role description',
		]);
		$this->save();
		$this->_roles->setDatas([
			'name' => 'Super Admin',
			'description' => 'All permissions are ignored with super admin user role and must be use only by a developer and only be edited or deleted directly on database',
		])->__save();

		$this->_roles->setDatas([
			'name' => 'Admin',
			'description' => 'Unlike "super admin", admin can be edited, changed or deleted.',
		])->__save();

		$this->_roles->setDatas([
			'name' => 'Normal',
			'description' => 'For ordinary users only. Cannot access admin/super admin rights.',
		])->__save();
	}

	protected function createPermissionsTable(){
		$this->setTablename('permissions');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 10,
			'ai' => self::_AI,
			'comment' => 'permission id',
		]);
		$this->addColumn([
			'name'=>'name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'permission name',
		]);
		$this->addColumn([
			'name'=>'description',
			'type'=> self::_TEXT,
			'nullable' => true,
			'comment' => 'description',
		]);
		$this->addColumn([
			'name'=>'code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'permission code',
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();

		$this->_permissions->setDatas([
			'name' => 'Manage Users',
			'description' => 'Role to manage site users',
			'code' => 'MANAGEUSER'
		])->__save();

		$this->_permissions->setDatas([
			'name' => 'Manage Roles',
			'description' => 'Role to manage site roles',
			'code' => 'MANAGEROLE'
		])->__save();

		$this->_permissions->setDatas([
			'name' => 'Manage Permissions',
			'description' => 'Role to manage site permissions',
			'code' => 'MANAGEPERMISSIONS'
		])->__save();

		$this->_permissions->setDatas([
			'name' => 'Manage Site Settings',
			'description' => 'Role to manage site settings',
			'code' => 'MANAGESITESETTINGS'
		])->__save();

		$this->_permissions->setDatas([
			'name' => 'Login to admin',
			'description' => 'Permission either to allow role to login in admin panel',
			'code' => 'LOGINTOADMIN'
		])->__save();

		$this->_permissions->setDatas([
			'name' => 'Manage CMS',
			'description' => 'Role to manage cms pages',
			'code' => 'MANAGECMS'
		])->__save();
	}

	protected function createPermissionTable(){
		$this->setTablename('permission');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 10,
			'ai' => self::_AI,
			'comment' => 'permission id',
		]);
		$this->addColumn([
			'name'=>'role_id',
			'type'=> self::_INT,
			'length' => 10,
			'comment' => 'role id',
		]);
		$this->addColumn([
			'name'=>'permissions_id',
			'type'=> self::_INT,
			'length' => 10,
			'comment' => 'permissions id',
		]);
		$this->addColumn([
			'name'=>'permissions_code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'permission code',
		]);
		$this->addColumn([
			'name'=>'create',
			'type'=> self::_TINYINT,
			'length' => 1,
			'comment' => 'bool 1=true 0=false',
		]);
		$this->addColumn([
			'name'=>'read',
			'type'=> self::_TINYINT,
			'length' => 1,
			'comment' => 'bool 1=true 0=false',
		]);
		$this->addColumn([
			'name'=>'update',
			'type'=> self::_TINYINT,
			'length' => 1,
			'comment' => 'bool 1=true 0=false',
		]);
		$this->addColumn([
			'name'=>'delete',
			'type'=> self::_TINYINT,
			'length' => 1,
			'comment' => 'bool 1=true 0=false',
		]);
		$this->addColumn([
			'name'=>'view',
			'type'=> self::_TINYINT,
			'length' => 1,
			'comment' => 'bool 1=true 0=false',
		]);
		$this->save();
		/* $this->addForeignKey('permission', 'role_id', 'roles', 'id');
		$this->addForeignKey('permission', 'permissions_id', 'permissions', 'id'); */
	}

	protected function createHelpPagesTable(){
		$this->setTablename('helppages');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 10,
			'ai' => self::_AI,
			'comment' => 'help page id',
		]);
		$this->addColumn([
			'name'=>'page',
			'type'=> self::_VARCHAR,
			'length' => 60,
			'nullable' => false,
			'comment' => 'page title',
		]);
		$this->addColumn([
			'name'=>'pageContent',
			'type'=> 'LONGTEXT',
			'nullable' => false,
			'comment' => 'help page content',
		]);
		$this->addColumn([
			'name'=>'slug',
			'type'=> self::_VARCHAR,
			'length' => 60,
			'nullable' => false,
			'comment' => 'page slug',
		]);

		$this->save();
	}

	protected function createUsersTable(){
		$this->setTablename('user');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
			'comment' => 'release id',
		]);
		$this->addColumn([
			'name'=>'password',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'release password',
		]);
		$this->addColumn([
			'name'=>'real_password',
			'type'=> self::_VARCHAR,
			'length' => 50,
			'nullable' => true
		]);
		$this->addColumn([
			'name'=>'email',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'release email',
		]);
		$this->addColumn([
			'name'=>'username',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
			'comment' => 'username',
		]);
		$this->addColumn([
			'name'=>'status',
			'type'=> self::_TINYINT,
			'default'=> 0,
			'length' => 1,
			'nullable' => false,
			'comment' => 'release status',
		]);
		$this->addColumn([
			'name'=>'archive',
			'type'=> self::_TINYINT,
			'default'=> '0',
			'length' => 1,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'update_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->addColumn([
			'name'=>'created_by',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'created by',
		]);
		$this->addColumn([
			'name'=>'update_by',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'update by',
		]);
		$this->addColumn([
			'name'=>'user_role_id',
			'type'=> self::_INT,
			'length' => 10,
			'default'=> Userrole::USER,
			'comment' => 'user role id',
		]);
		$this->addColumn([
			'name'=>'last_login',
			'type'=> self::_TIMESTAMP,
			'nullable' => true,
			'default'=> null,
			'comment' => 'last login',
		]);
		$this->save();
		/* $this->addForeignKey('user', 'user_role_id', 'roles', 'id'); */
		$this->_users->setDatas([
			'password' => $this->_password->setPassword('Qq123456789')->getHash(),
			'email'	=> 'superadmin@email.com',
			'status' => Status::ENABLED,
			'user_role_id' => 1
		])->__save();
	}
	
	protected function createUsersProfileTable(){
		$this->setTablename('user_profile');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'user_id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'user id',
		]);
		$this->addColumn([
			'name'=>'salutation',
			'type'=> self::_VARCHAR,
			'length' => 10,
			'nullable' => true,
			'comment' => 'Mr. Ms. Prof. Dr.',
		]);
		$this->addColumn([
			'name'=>'first_name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'last_name',
			'type'=> self::_VARCHAR,
			'length' => 50,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'suffix',
			'type'=> self::_VARCHAR,
			'length' => 10,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'position',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'about',
			'type'=> self::_TEXT,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'information',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'company',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'push_notification',
			'type'=> self::_TINYINT,
			'length' => 1,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'birthdate',
			'type'=> self::_DATE,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'gender',
			'type'=> self::_VARCHAR,
			'length' => 10,
			'nullable' => true,
			'comment' => 'male , female',
		]);
		$this->addColumn([
			'name'=>'profile_pic',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'default' => '',
			'nullable' => true,
			'comment' => 'profile pic',
		]);
		$this->addColumn([
			'name'=>'unique_id',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'lon',
			'type'=> self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name'=>'lat',
			'type'=> self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name'=>'location',
			'type'=> self::_TEXT,
			'nullable' => true
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);

		$this->addColumn([
			'name'=>'profile_deleted',
			'type'=> self::_TINYINT,
			'default'=> '0',
			'length' => 1,
			'nullable' => true,
		]);

		$this->save();

		$this->_userProfile->setDatas([
			'user_id' => 1
		])->__save();
		/* $this->addForeignKey('user_profile', 'user_id', 'user', 'id'); */

	
	}

	protected function createUsersAddressTable(){
		$this->setTablename('address');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
			'comment' => 'Id',
		]);
		$this->addColumn([
			'name'=>'profile_id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
			'comment' => 'Id',
		]);
		$this->addColumn([
			'name'=>'address',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'city',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'region',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'zip_code',
			'type'=> self::_VARCHAR,
			'length' => 20,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'state',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'country_id',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();

		$this->_address->setDatas([
			'profile_id' => 1
		])->__save();
	}

	protected function createUsersContactTable(){
		$this->setTablename('contact');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 10,
			'ai' => self::_AI,
			'comment' => 'Id',
		]);
		$this->addColumn([
			'name'=>'profile_id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'release name',
		]);
		$this->addColumn([
			'name'=>'number',
			'type'=> self::_VARCHAR,
			'length' => 20,
			'nullable' => true,
			'comment' => 'release name',
		]);
		$this->addColumn([
			'name'=>'type',
			'type'=> self::_VARCHAR,
			'length' => 10,
			'nullable' => true,
			'comment' => 'mobile, office, home',
		]);
		$this->save();

		$this->_contact->setDatas([
			'profile_id' => 1
		])->__save();
	}

	protected function createDeviceTokenTable(){
		$this->setTablename('device_token');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
			'comment' => 'Id',
		]);
		$this->addColumn([
			'name'=>'user_ids',
			'type'=> self::_VARCHAR,
			'length' => 30,
			'nullable' => false,
			'comment' => 'release name',
		]);
		$this->addColumn([
			'name'=>'token',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'release version',
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'api_key',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'release version',
		]);
		$this->addColumn([
			'name'=>'api_secret',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'release version',
		]);
		$this->save();
	}
	
	protected function createAttachmentsTable(){
		$this->setTablename('attachments');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 10,
			'ai' => self::_AI,
			'comment' => 'Id',
		]);
		$this->addColumn([
			'name'=>'uploader_id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'user who uploaded the attachment',
		]);
		$this->addColumn([
			'name'=>'attachment_type',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'type of attachment (profile picture, banner, etc)',
		]);
		$this->addColumn([
			'name'=>'filename',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'file name',
		]);
		$this->addColumn([
			'name'=>'filepath',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'file path',
		]);
		$this->addColumn([
			'name'=>'uploaded_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date and time uploaded',
		]);
		$this->addColumn([
			'name'=>'profile_id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'the user profile id of user',
		]);
		$this->addColumn([
			'name'=>'tablename',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
			'comment' => 'from what table this attachment is related to',
		]);
		$this->addColumn([
			'name'=>'primary_key',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => true,
			'comment' => 'the primary key of the ralated table',
		]);
		$this->save();
	}

	protected function createEmailTemplateTable(){
		$this->setTablename('email_templates');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 11,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'template_name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'template_code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'subject',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'template',
			'type'=> 'LONGTEXT',
			'nullable' => false,
			'comment' => 'email template',
		]);
		$this->addColumn([
			'name'=>'from_name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'The name where the email came from',
		]);
		$this->addColumn([
			'name'=>'email',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);

		$this->save();
	}

	protected function createNotificationTable(){
		$this->setTablename('notifications');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 10,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'item_id',
			'type'=> self::_INT,
			'length' => 255,
			'nullable' => false,
			'comment' => 'identification of the notif',
		]);
		$this->addColumn([
			'name'=>'owner_user_id',
			'type'=> self::_INT,
			'length' => 11,
			'nullable' => false,
			'comment' => 'where the notification from',
		]);
		$this->addColumn([
			'name'=>'type',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'url',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => true,
			'comment' => 'url where redirect the reader',
		]);
		$this->addColumn([
			'name'=>'data',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'message or content of notif',
		]);
		$this->addColumn([
			'name'=>'read_by_user_id',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'who read the notifications',
		]);
		$this->addColumn([
			'name'=>'isRead',
			'type'=> self::_TINYINT,
			'length' => 1,
			'nullable' => false,
			'default' => 0 /* MINSAN NAWAWALA ANG DEFAULT VALUE. PLEASE CHECK SA DATABASE */
		]);
		$this->addColumn([
			'name'=>'read_at',
			'type'=> self::_TIMESTAMP,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}

	protected function createRegionTable(){
		$this->setTablename('lib_regions');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 10,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'name of the region',
		]);
		$this->addColumn([
			'name'=>'code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'short_name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}

	protected function createStateTable(){
		$this->setTablename('lib_state');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 10,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'name of the region',
		]);
		$this->addColumn([
			'name'=>'shipping_region_id',
			'type'=> self::_INT,
			'length' => 11,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'region_code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}

	protected function createCityTable(){
		$this->setTablename('lib_city');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 10,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'name',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'name of the region',
		]);
		$this->addColumn([
			'name'=>'region_code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'province_code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}

	protected function createZipCodeTable(){
		$this->setTablename('lib_zip_code');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 10,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'country',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
			'comment' => 'name of the region',
		]);
		$this->addColumn([
			'name'=>'major_area',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'city_id',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'zip_code',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->addColumn([
			'name'=>'city',
			'type'=> self::_VARCHAR,
			'length' => 255,
			'nullable' => false,
		]);
		$this->save();
	}
	public function createActivationCodeTable() {
		$this->setTablename('user_activation_code');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'ai' => self::_AI,
		]);
		$this->addColumn([
			'name'=>'user_id',
			'type'=> self::_BIGINT,
			'length' => 20,
			'nullable' => false,
			'comment' => 'user id',
		]);
		$this->addColumn([
			'name'=>'activation_code',
			'type'=> self::_VARCHAR,
			'length' => 6,
			'nullable' => false,
			'comment' => '6 digit activation code',
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
	public function createCmsTable() {
		$this->setTablename('cms');
		$this->setPrimarykey('id');
		$this->addColumn([
			'name'=>'id',
			'type'=> self::_INT,
			'length' => 10,
			'ai' => self::_AI,
			'comment' => 'help page id',
		]);
		$this->addColumn([
			'name'=>'title',
			'type'=> self::_VARCHAR,
			'length' => 25,
			'nullable' => false,
			'comment' => 'title of page',
		]);
		$this->addColumn([
			'name'=>'page',
			'type'=> self::_VARCHAR,
			'length' => 25,
			'nullable' => false,
			'comment' => 'code of page',
		]);
		$this->addColumn([
			'name'=>'content',
			'type'=> 'LONGTEXT',
			'nullable' => false,
			'comment' => 'html content',
		]);
		$this->addColumn([
			'name'=>'created_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'comment' => 'date created',
		]);
		$this->addColumn([
			'name'=>'updated_at',
			'type'=> self::_TIMESTAMP,
			'default'=> self::_CURRENT_TIMESTAMP,
			'nullable' => false,
			'onupdate' => self::_CURRENT_TIMESTAMP,
			'comment' => 'date of update',
		]);
		$this->save();
	}
}