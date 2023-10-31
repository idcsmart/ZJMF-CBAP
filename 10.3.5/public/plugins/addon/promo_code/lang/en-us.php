<?php

return [
	'id_error' => 'ID error',
	'param_error' => 'parameter error',
	'success_message' => 'request successful',
	'fail_message' => 'request failed',
	'create_success' => 'created successfully',
	'create_fail' => 'create failed',
	'delete_success' => 'delete success',
	'delete_fail' => 'Delete failed',
	'update_success' => 'Modified successfully',
	'update_fail' => 'modification failed',
	'cannot_repeat_opreate' => 'Cannot repeat operation',
	'promo_code_require' => 'Please fill in the discount code',
	'promo_code_error' => 'promo code can only be 9 characters and contain uppercase and lowercase letters and numbers',
	'promo_code_unique' => 'promo code already exists',
	'promo_code_start_time_require' => 'Please fill in the effective time',
	'promo_code_start_time_date' => 'effective time error',
	'promo_code_end_time_date' => 'Deadline time error',
	'promo_code_end_time_gt' => 'The deadline needs to be greater than the effective time',
	'promo_code_max_times_require' => 'Please fill in the maximum number of times',
	'promo_code_max_times_error' => 'The maximum number of uses must be an integer greater than or equal to 0',
	'promo_code_notes_max' => 'Note length is up to 1000 characters',
	'promo_code_is_not_exist' => 'promo code does not exist',
	'promo_code_type_percent_value_error' => 'The discount ratio can only be a number greater than 0 and less than or equal to 100',
	'promo_code_type_fixed_amount_value_error' => 'The deduction amount can only be a number greater than 0',
	'promo_code_type_replace_price_value_error' => 'The coverage amount can only be a number above 0',
	'promo_code_product_is_not_exist' => 'Commodity does not exist',
	'promo_code_type_percent_description' => 'Discount code {promo_code} applied to product Host Id:{host_id}, {value}% percentage discount',
	'promo_code_type_fixed_amount_description' => 'Discount code {promo_code} applied to product Host Id: {host_id}, {value} fixed amount reduction discount',
	'promo_code_type_replace_price_description' => 'Discount code {promo_code} applied to the product Host Id: {host_id}, {value} override price discount',
	'promo_code_type_free_description' => 'Discount code {promo_code} applied to product Host Id:{host_id}, free discount',
	'promo_code_type_fixed_amount_not_support' => 'Promo type fixed amount reduction does not support opening upgrading and downgrading, upgrading and downgrading cycle, renewal, renewal cycle',
    'promo_code_type_replace_price_not_support' => 'Promotion type coverage price does not support opening upgrading and downgrading, upgrading and downgrading cycles, renewals, and renewal cycles',
    'promo_code_type_free_not_support' => 'Promo type free does not support opening upgrading and downgrading cycles and renewal cycles',


	'log_admin_create_promo_code' => '{admin} new discount code: {promo_code}',
	'log_admin_update_promo_code' => '{admin}modifies discount code {promo_code}:{description}',
	'log_admin_delete_promo_code' => '{admin} delete promo code: {promo_code}',
	'log_admin_enable_promo_code' => '{admin} enable promo code: {promo_code}',
	'log_admin_disable_promo_code' => '{admin} disable promo code: {promo_code}',
	'promo_code_client_use_promo_code' => '{client} use promo code: {promo_code}, apply to order: {order_id}',


	# 优惠码可用判断
	'addon_promo_code_not_found' => 'No promo code found',
	'addon_promo_code_has_expired' => 'The promo code has expired',
	'addon_promo_code_product_cannot_use' => 'This discount code cannot be applied to this product',
	'addon_promo_code_the_condition_cannot_use' => 'The conditions for using the promo code have not yet been reached',
	'addon_promo_code_upgrade_cannot_use' => 'This discount code cannot be used when upgrading',
	'addon_promo_code_renew_cannot_use' => 'This discount code cannot be used when renewing products',
	'addon_promo_code_only_new_client' => 'This promotional code can only be used for new users without products',
	'addon_promo_code_only_old_client' => 'This promotional code can only be used for users who have activated products in the account',
	'addon_promo_code_higher_cannot_use' => 'The price after applying the discount code is higher than the original price and cannot be applied',

	# 导航
	'nav_plugin_addon_promo_code' => 'promo code',

];
