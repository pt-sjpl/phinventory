<?php

return [
    'bulk_delete'		=> '確認批次刪除資產',
    'bulk_restore'      => '確認批次還原資產', 
  'bulk_delete_help'	=> '請再次確認批次刪除的資產。刪除後資產可以恢復，但將會失去當前的用戶關聯資訊。',
  'bulk_restore_help'	=> '請再次確認批次還原的資產。還原後，這些資產將不再與它們先前分配的任何使用者相關聯。',
  'bulk_delete_warn'	=> '即將刪除 :asset_count 項資產',
  'bulk_restore_warn'	=> '您即將還原 :asset_count 項資產。',
    'bulk_update'		=> '批次更新資產',
    'bulk_update_help'	=> '此表格允許您同時修改多項資產。請僅填寫需要修改的欄位，留空的欄位不會做任何修改。',
    'bulk_update_warn'	=> '您即將編輯單一資產的屬性。|您即將編輯 :asset_count 項資產的屬性。',
    'bulk_update_with_custom_field' => 'Note the assets are :asset_model_count different types of models.',
    'bulk_update_model_prefix' => 'On Models', 
    'bulk_update_custom_field_unique' => 'This is a unique field and can not be bulk edited.',
    'checkedout_to'		=> '借出至',
    'checkout_date'		=> '借出日期',
    'checkin_date'		=> '繳回日期',
    'checkout_to'		=> '借出至',
    'cost'				=> '採購成本',
    'create'			=> '新增資產',
    'date'				=> '採購日期',
    'depreciation'	    => '折舊',
    'depreciates_on'	=> '折舊於',
    'default_location'	=> '預設位置',
    'default_location_phone' => 'Default Location Phone',
    'eol_date'			=> '產品壽命日期',
    'eol_rate'			=> '產品壽命等級',
    'expected_checkin'  => '預計歸還日期',
    'expires'			=> '到期',
    'fully_depreciated'	=> '提足折舊',
    'help_checkout'		=> '如果您希望立即分配此資產，請在上方狀態欄位選擇 "可部署"。',
    'mac_address'		=> 'MAC地址',
    'manufacturer'		=> '製造商',
    'model'				=> '型號',
    'months'			=> '月數',
    'name'				=> '資產名稱',
    'notes'				=> '備註',
    'order'				=> '訂單編號',
    'qr'				=> 'QR Code',
    'requestable'		=> '使用者可申請此資產',
    'redirect_to_all'   => 'Return to all :type',
    'redirect_to_type'   => 'Go to :type',
    'redirect_to_checked_out_to'   => 'Go to Checked Out to',
    'select_statustype'	=> '選擇狀態類型',
    'serial'			=> '序號',
    'status'			=> '狀態',
    'tag'				=> '資產標籤',
    'update'			=> '更新資產',
    'warranty'			=> '保固',
        'warranty_expires'		=> '保固期限',
    'years'				=> '年',
    'asset_location' => '更新資產位置',
    'asset_location_update_default_current' => '更新預設位置和實際位置',
    'asset_location_update_default' => '只更新預設位置',
    'asset_location_update_actual' => 'Update only actual location',
    'asset_not_deployable' => '該資產狀態無法部署。此資產無法被借出。',
    'asset_not_deployable_checkin' => 'That asset status is not deployable. Using this status label will checkin the asset.',
    'asset_deployable' => 'This asset can be checked out.',
    'processing_spinner' => '處理中... (大型檔案可能需要一些時間)',
    'processing' => '處理中... ',
    'optional_infos'  => '選填資訊',
    'order_details'   => '訂單相關資訊',
    'calc_eol'    => 'If nulling the EOL date, use automatic EOL calculation based on the purchase date and EOL rate.',
];
