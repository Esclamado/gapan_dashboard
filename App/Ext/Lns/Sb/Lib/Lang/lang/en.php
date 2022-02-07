<?php
	/* _na_ = not available */
	return [
		'success' => 'Success!',

		'api_auth_failed' => 'Authentication failed.',
		'api_invalid_appkey' => 'Invalid app key.',
		'api_invalid_deviceid' => 'Invalid device ID',
		'api_invalid_devicetoken' => 'Invalid device token',
		'api_invalid_token' => 'Invalid token.',
		'api_expired_token' => 'Expired token.',
		'api_token_early' => 'To early for this token.',
		'invalid_request' => 'Invalid request.',

		/* login */
		'login_no' => 'You are not logged in!',
		'login_no_email' => 'Email is required.',
		'login_success' => 'You are now logged in!',
		'login_wrong_pass' => 'Please enter a correct password.',

		/* logout */
		'logout_yes' => 'Successfully logout!',

		/* account */
		'account_create' => 'Successfully registered!',
		'account_not_found' => 'The username and password you entered did not match our records. Please try again.',
		'account_need_activate' => 'Your account needs activation.',
		'account_no_access' => "Unable to grant access to your account.",
		'password_current_incorrect' => 'Login Credentials did not match in our records.',
		'password_new_confirm_not_match' => 'Unable to confirm your new password.',
		'same_change_password' => 'Your new password is the same with your current password.',
		'verification_code_sent' => 'A verification code has been sent to your email.',
		'verification_code_valid' => 'Verification code is valid.',
		'verification_code_invalid' => 'Invalid verification code.',
		'change_password_success' => 'Your password has been successfully changed.',

		/* email */
		'email_exists' => 'Email address already exists.',
		'email_available' => 'Email is available.',
		'email_verified' => 'Your email is now verified!',
		'email_already_verified' => 'Your email is already verified.',
		'email_not_found' => "Email address doesn't exist.",
		'email_verify_email_sent' => 'A verification email with activation url was sent to your email. Please click the activation url to complete your registration.',
		'resend_credential_failed' => 'Failed to resend credentials.',
		
		/* entities */
		'no_data_retrieved' => 'No data was retrieved.',
		'save_failed' => 'Failed to save to the database.',

		/* patient */
		'patient_not_found' => 'Patient not found.',
		'patient_already_deleted' => "Patient's profile has already been deleted.",
		'patient_delete_failed' => "Failed to delete patient's profile.",
		'patient_already_recovered' => "Patient's profile has already been recovered.",
		'patient_recover_failed' => "Failed to recover patient's profile.",
		'patient_no_ticket' => "Patient doesn't have lost ID ticket.",
		'patient_ticket_already_claimed' => "Patient has already claimed his/her ID.",
		'unknown_patient_address' => "Patient's address is not specified. Please contact the administrator.",
		'invalid_qr_code' => 'Invalid QR Code.',
		'visitation_time_in_failed' => 'Unable to start patient visitation.',
		'visitation_time_out_failed' => 'Unable to end patient visitation.',
		'visitationlog_ended' => 'Patient visitation has already ended.',
		'visitationlog_not_found' => "You have no active visitation as of this moment. Please scan the patient's QR code.",
		'visitationlog_missed_no_message' => "Please add a reason for missed visitation.",
		'no_visitation_logs' => "You don't have any activity logs yet.",

		/* fieldstaff */
		'fieldstaff_not_found' => 'Field staff not found.',
		'fieldstaff_already_deleted' => "Field staff's profile has already been deleted.",
		'fieldstaff_delete_failed' => "Failed to delete field staff's profile.",
		'fieldstaff_already_recovered' => "Field staff's profile has already been recovered.",
		'fieldstaff_recover_failed' => "Failed to recover field staff's profile.",
		'fieldstaff_cannot_save' => "Sorry the system is unable to save field staff's information",
		'outside_geofence' => "You are {{var}} away from the patient's geofence.",
		'unknown_current_location' => "Your location is unknown. Please enable geolocation.",
		'not_within_area' => "You're not within the patient's area.",
		'time_out_invalid' => "Unable to end patient visitation. Invalid visitation time out.",

		/* ticket */
		'ticket_save_failed' => 'Unable to save new ticket',

		/* import */
		'no_file_uploaded' => 'No file was uploaded.',
		'invalid_file_type' => 'Invalid file type.',
		'no_rows_saved' => 'No rows were saved.',
		'no_records_in_csv' => 'No records to be saved.',

		/* pagination */
		'pagination_no_params' => 'Please specify page and limit.',

		/* activation */
		'account_resent_activation' => 'A new activation code was sent to your email.',
		'account_activated' => 'Your account is now activated!',
		'account_activation_code_invalid' => 'Your activation code seems to be invalid. Would you like to try again?',







		'facebook_id_exist' => 'Facebook account was already linked to other account.',
		'google_id_exist' => 'Google account was already linked to other account.',

		'sign_up_no_firstname' => 'Please enter your firstname.',
		'sign_up_no_lastname' => 'Please enter your lastname.',
		'sign_up_notifs' => 'Welcome to Floky!',
		'invalid_email_format' => 'Please enter a valid email format.',
		'invalid_password_format' => 'Password must be atleast 8 characters long.',
		'username_exists' => 'Username already exists.',
		'create_user_failed' => 'Unable to create user account. Please try again later',
		'account_created' => 'You are now registered to Floky! you may now login your account.',
		'account_created_email_sent' => 'A verification email with activation url was sent to your email. Please click the activation url to complete your registration.',
		
		'account_login_success' => 'You are now logged in!',
		'account_blocked_by_admin' => 'You r account has been blocked. Please contact the App Administrator to retrieve your account.',
		'account_na_for_display' => '{{var}} profile seems unavailable now. For the meantim, try viewing other users.',
		'account_social_login_failed' => 'Something went wrong during login, please try again',
		'diabled_by_admin' => 'Your account has been disabled by the Administrator.',
		'user_not_found' => 'User is not yet registered',
		'upload_error_0' => 'Your upload is successful!',
		'upload_error_1' => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		'upload_error_2' => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		'upload_error_3' => 'The uploaded file was only partially uploaded',
		'upload_error_6' => 'Missing a temporary folder',
		'upload_error_7' => 'Failed to write file to disk.',
		'upload_error_8' => 'A PHP extension stopped the file upload.',
		'upload_5_images_above' => 'You must upload atleast {{var}} images.',
		'uploaded_was_lessthan_5' => 'Number of success uploaded images was less than minimum ({{var}}).',
		'multi_upload_success' => 'Photos uploaded successfully.',
		'something_wrong' => 'Something went wrong. Please try again later.',
		'something_went_wrong' => 'Something went wrong.',
		'updated_profile' => 'Your profile is now updated.',
		'no_liked_items' => 'You have not liked any ads yet.',
		'success_cat_search' => 'Results with "{{var}}" keyword is found.',
		'cat_search_failed' => 'No results with "{{var}}" keyword is found.',
		'category_not_found' => 'Category not found.',
		'recent_no_item' => 'You haven\'t liked any ads yet.',
		'error_encountered' => 'Something went wrong, please try again.',
		'logs_not_found' => 'No notifications found',
		'logs_not_for_user' => 'Your are trying to read other user\'s notification.',
		'logs_already_read' => 'This notification is already read.',
		'notif_report_item' => 'reported an ad.',
		'notif_item_add' => 'posted an ad.',
		'notif_comment_post' => 'posted a comment on your ad.',
		'email_verify_invalid_code' => 'Invalid code.',
		'email_verify_invalid_digest' => 'Invalid digest.',
		'device_unregistered_success' => 'Device is now removed.',
		'device_unregistered_not_found' => 'Unable to find device',
		'device_unregistered_not_now' => 'Failed to remove device, please try again later.',
		'cms_not_exists' => 'Page does not exist.',

		/* GENERAL WORDS : START */
		'back' => 'Back',
		'go_back' => 'Go back',
		'go_back_to_list' => 'Go Back to List',
		'save' => 'Save',
		'title' => 'Title',
		'content' => 'Content',
		'yes' => 'Yes',
		'no' => 'No',
		'api_key' => 'API Key',
		'api_secret' => 'API Secret',
		'email' => 'Email',
		'save_changes' => 'Save Changes',
		'cancel' => 'Cancel',
		/* GENERAL WORDS : END */

		/* NAVIGATION : START */
		'dashboard' => 'Dashboard',
		'users' => 'Users',
		'manage_users' => 'Manage Users',
		'add_user' => 'Add User',
		'role_permission' => 'Role Permission',
		'roles' => 'Roles',
		'permissions' => 'Permissions',
		'cms' => 'CMS',
		'manage_cms' => 'Manage Cms',
		'add_cms' => 'Add CMS',
		'settings' => 'Settings',
		'email_templates' => 'Email Templates',
		/* NAVIGATION : END */

		/* MESSAGE : START */
		/* LOGIN */
		'admin_login_error' => 'You are not allowed to login to admin area.',
		'admin_login_failed' => 'Invalid email or password.',
		'admin_login_success' => 'Welcome back.',
		'admin_login_disabled' => 'This account is currently disabled.',
		'admin_login_email_empty' => 'Email is empty.',
		/* SETTINGS */
		'settings_save_error' => 'Your are not allowed to edit settings.',
		'settings_save_success' => 'Settings successfully saved.',

		/* CMS */
		'cms_create_error' => 'Your are not allowed to create new cms.',
		'cms_create_success' => 'Cms successfully saved.',
		'cms_create_failed' => 'Cannot save the Cms try again later.',
		'cms_create_exist_error' => 'Page already exists.',
		'cms_update_error' => 'Your are not allowed to update cms.',
		'cms_update_success' => 'Cms successfully updated.',
		'cms_update_noexist_error' => 'Cannot find the Cms you are trying to update.',
		'cms_delete_error' => 'Your are not allowed to delete cms.',
		'cms_delete_noexist_error' => 'Cannot find the Cms you are trying to delete.',
		'cms_delete_success' => 'Cms deleted successfully.',
		/* MESSAGE : END */

		/* CMS : START */
		'create_new_cms' => 'Create New Cms',
		'edit_cms' => 'Edit Cms',
		'cms_fields' => 'CMS Fields',
		'page_url' => 'Page URL',
		'the_new_url_path' => 'The new url path',
		/* CMS : END */

		/* LOGIN : START */
		'hey_good_to_see_you_again' => 'Hey, good to see you again!',
		'log_in_to_get_going' => 'Log in to get going',
		'please_enter_an_email' => 'Please enter an email.',
		'password' => 'Password',
		'please_enter_you_password' => 'Please enter your password.',
		'remember_me' => 'Remember Me',
		'forgot_password' => 'Forgot password?',
		/* LOGIN : END */

		/* FORGOT PASSWORD : START */
		'forgot_your_password' => 'Forgot your password?',
		'enter_your_email_address_below' => 'Enter your email address below, and we\'ll send you instructions for setting a new one.',
		'send_me_the_code' => 'Send me the code',
		'code' => 'Code',
		'verify_code' => 'Verify code.',
		'new_password' => 'New Password',
		'retype_password' => 'Re-type Password',
		'change_your_password' => 'Change your password.',
		/* FORGOT PASSWORD : END */

		/* ROLE : START */
		'role_fields' => 'Role Fields',
		'role_name' => 'Role Name',
		'role_description' => 'Role Description',
		/* ROLE : END */

		/* PERMISSION : START */
		'permission_fields' => 'Permission Fields',
		'permission_name' => 'Permission Name',
		'permission_description' => 'Permission Description',
		/* PERMISSION : END */
		/* SETTINGS : START */
		'general_settings' => 'General Settings',
		'site_name' => 'Site Name',
		'site_email' => 'Site Email',
		'site_support_email' => 'Support Email',
		'site_copyright' => 'Copyright',
		'site_data_limit' => 'Data Limit',

		'api_settings' => 'API Settings',
		'site_api_exp_enabled' => 'Enable token expiration',
		'site_api_token_max_time' => 'Not Logged In Token Max Age',
		'site_api_token_max_time_help' => 'The Get Token API expiration time in milisec. This will will be added in time() token validation',
		'site_api_logged_id_token_max_age' => 'Logged In Token Max Age',
		'site_api_logged_id_token_max_age_help' => 'Max age for logged in token. This will will be added in token expiration date',

		'seo_meta_settings' => 'SEO / Meta Settings',
		'site_meta_keywords' => 'Meta Keywords',
		'site_meta_description' => 'Meta Description',

		'facebook' => 'Facebook',
		'google' => 'Google',

		'language_setting' => 'Language Setting',
		'default_language' => 'Default Language',
		'english' => 'English',

		'site_images' => 'Site Images',
		'favicon' => 'Favicon',
		'logo' => 'Logo',
		/* SETTINGS : END */

		/* EMAIL TEMPLATE : START */
		'create_email_template' => 'Create Email Template',
		'template_name' => 'Template Name',
		'email_from_name' => 'From Name',
		'email_from' => 'Email (From)',
		'subject' => 'Subject',
		'email_template' => 'Email Template',
		'email_from_name_placeholder' => 'John Doe'
		/* EMAIL TEMPLATE : END */

		
	];
?>