<?php
return [
	'vendor' => 'Lns',
	'module' => 'Sb',
	'version' => '1.0.4',
	'controllers' => [
		'admin_index_index_index' => 'Lns\\Sb\\Controller\\Admin\\Index\\Index\\Index', 
		'admin_login_index_index' => 'Lns\\Sb\\Controller\\Admin\\Login\\Index\\Index', 
		'admin_logout_index_index' => 'Lns\\Sb\\Controller\\Admin\\Logout\\Index\\Index',
		'admin_login_auth_index' => 'Lns\\Sb\\Controller\\Admin\\Login\\Auth\\Index',
		'admin_forgotpassword_index_index' => 'Lns\\Sb\\Controller\\Admin\\ForgotPassword\\Index\\Index', 

		'admin_permissions_action_add' => 'Lns\\Sb\\Controller\\Admin\\Permissions\\Action\\Add',
		'admin_permissions_action_delete' => 'Lns\\Sb\\Controller\\Admin\\Permissions\\Action\\Delete',
		'admin_permissions_action_edit' => 'Lns\\Sb\\Controller\\Admin\\Permissions\\Action\\Edit',
		'admin_permissions_action_listing' => 'Lns\\Sb\\Controller\\Admin\\Permissions\\Action\\Listing',
		'admin_permissions_action_save' => 'Lns\\Sb\\Controller\\Admin\\Permissions\\Action\\Save',
		'admin_permissions_index_index' => 'Lns\\Sb\\Controller\\Admin\\Permissions\\Index\\Index',

		'admin_roles_index_index' => 'Lns\\Sb\\Controller\\Admin\\Roles\\Index\\Index',
		'admin_roles_action_add' => 'Lns\\Sb\\Controller\\Admin\\Roles\\Action\\Add',
		'admin_roles_action_delete' => 'Lns\\Sb\\Controller\\Admin\\Roles\\Action\\Delete',
		'admin_roles_action_edit' => 'Lns\\Sb\\Controller\\Admin\\Roles\\Action\\Edit',
		'admin_roles_action_listing' => 'Lns\\Sb\\Controller\\Admin\\Roles\\Action\\Listing',
		'admin_roles_action_save' => 'Lns\\Sb\\Controller\\Admin\\Roles\\Action\\Save',
		'admin_roles_permission_assign' => 'Lns\\Sb\\Controller\\Admin\\Roles\\Permission\\Assign',

		'admin_settings_index_index' => 'Lns\\Sb\\Controller\\Admin\\Settings\\Index\\Index',
		'admin_settings_action_save' => 'Lns\\Sb\\Controller\\Admin\\Settings\\Action\\Save',

		'admin_users_action_listing' => 'Lns\\Sb\\Controller\\Admin\\Users\\Action\\Listing',
		'admin_users_test_getlist' => 'Lns\\Sb\\Controller\\Admin\\Users\\Test\\GetList',

		'admin_users_index_index' => 'Lns\\Sb\\Controller\\Admin\\Users\\Index\\Index',
		'admin_users_create_index' => 'Lns\\Sb\\Controller\\Admin\\Users\\Create\\Index',
		'admin_users_view_index' => 'Lns\\Sb\\Controller\\Admin\\Users\\View\\Index',
		'admin_users_edit_index' => 'Lns\\Sb\\Controller\\Admin\\Users\\Edit\\Index',
		'admin_users_save_index' => 'Lns\\Sb\\Controller\\Admin\\Users\\Save\\Index',
		'admin_users_delete_index' => 'Lns\\Sb\\Controller\\Admin\\Users\\Delete\\Index',
		'admin_users_userprofile_index' => 'Lns\\Sb\\Controller\\Admin\\Users\\UserProfile\\Index',
		'admin_users_userprofile_changepassword' => 'Lns\\Sb\\Controller\\Admin\\Users\\UserProfile\\ChangePassword',
		'admin_users_address_getstate' => 'Lns\\Sb\\Controller\\Admin\\Users\\Address\\Getstate',
		'admin_users_address_getcity' => 'Lns\\Sb\\Controller\\Admin\\Users\\Address\\Getcity',

		'admin_settings_templates_index' => 'Lns\\Sb\\Controller\\Admin\\Settings\\Templates\\Index',
		'admin_settings_templates_save' => 'Lns\\Sb\\Controller\\Admin\\Settings\\Templates\\Save',
		'admin_settings_templates_create' => 'Lns\\Sb\\Controller\\Admin\\Settings\\Templates\\Create',
		'admin_settings_templates_preview' => 'Lns\\Sb\\Controller\\Admin\\Settings\\Templates\\Preview',
		'admin_settings_templates_edit' => 'Lns\\Sb\\Controller\\Admin\\Settings\\Templates\\Edit',
		'admin_settings_templates_delete' => 'Lns\\Sb\\Controller\\Admin\\Settings\\Templates\\Delete',
		'admin_settings_templates_listing' => 'Lns\\Sb\\Controller\\Admin\\Settings\\Templates\\Listing',

		'admin_file_upload_index' => 'Lns\\Sb\\Controller\\Admin\\File\\Upload\\Index',

		'admin_cms_action_add' => 'Lns\\Sb\\Controller\\Admin\\Cms\\Action\\Add',
		'admin_cms_action_delete' => 'Lns\\Sb\\Controller\\Admin\\Cms\\Action\\Delete',
		'admin_cms_action_edit' => 'Lns\\Sb\\Controller\\Admin\\Cms\\Action\\Edit',
		'admin_cms_action_listing' => 'Lns\\Sb\\Controller\\Admin\\Cms\\Action\\Listing',
		'admin_cms_action_save' => 'Lns\\Sb\\Controller\\Admin\\Cms\\Action\\Save',
		'admin_cms_index_index' => 'Lns\\Sb\\Controller\\Admin\\Cms\\Index\\Index',

		'api_token_get' => 'Lns\\Sb\\Controller\\Api\\Token\\Get',
		'api_user_register' => 'Lns\\Sb\\Controller\\Api\\User\\Register',
		'api_user_login' => 'Lns\\Sb\\Controller\\Api\\User\\Login',
		'api_user_activation' => 'Lns\\Sb\\Controller\\Api\\User\\Activation',
		'api_user_sociallogin' => 'Lns\\Sb\\Controller\\Api\\User\\Sociallogin',
		'api_user_logout' => 'Lns\\Sb\\Controller\\Api\\User\\Logout',
		'api_user_updateprofile' => 'Lns\\Sb\\Controller\\Api\\User\\Updateprofile',
		'api_user_validateemail' => 'Lns\\Sb\\Controller\\Api\\User\\Validateemail',
		'api_user_forgotpassword' => 'Lns\\Sb\\Controller\\Api\\User\\Forgotpassword',
		'api_user_changepassword' => 'Lns\\Sb\\Controller\\Api\\User\\Changepassword',
		'api_cms_page' => 'Lns\\Sb\\Controller\\Api\\CMS\\Page',
		'api_cms_displaypage' => 'Lns\\Sb\\Controller\\Api\\CMS\\Displaypage'
	],
];
?>