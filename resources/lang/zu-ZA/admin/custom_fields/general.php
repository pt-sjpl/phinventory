<?php

return [
    'custom_fields'		        => 'Amasimu wangokwezifiso',
    'manage'                    => 'Manage',
    'field'		                => 'Inkambu',
    'about_fieldsets_title'		=> 'Mayelana nama-Fieldsets',
    'about_fieldsets_text'		=> 'Fieldsets allow you to create groups of custom fields that are frequently re-used for specific asset model types.',
    'custom_format'             => 'Custom Regex format...',
    'encrypt_field'      	        => 'Bhala ukubaluleka kwalensimu ku-database',
    'encrypt_field_help'      => 'ISEXWAYISO: Ukubethela insimu kungenza kungabhekeki.',
    'encrypted'      	        => 'Kubhalisiwe',
    'fieldset'      	        => 'Fieldset',
    'qty_fields'      	      => 'Izinkambu ze-Qty',
    'fieldsets'      	        => 'Fieldsets',
    'fieldset_name'           => 'Igama le-Fieldset',
    'field_name'              => 'Igama leNsimu',
    'field_values'            => 'Izindinganiso zensimu',
    'field_values_help'       => 'Engeza okukhethwa kukho okukhethwa kukho, umugqa ngamunye. Imigqa engacacile ngaphandle kwelayini yokuqala izobe ishaywa indiva.',
    'field_element'           => 'Ifomu Element',
    'field_element_short'     => 'I-Element',
    'field_format'            => 'Fometha',
    'field_custom_format'     => 'Ifomethi Yokwezifiso',
    'field_custom_format_help'     => 'This field allows you to use a regex expression for validation. It should start with "regex:" - for example, to validate that a custom field value contains a valid IMEI (15 numeric digits), you would use <code>regex:/^[0-9]{15}$/</code>.',
    'required'   		          => 'Kudingeka',
    'req'   		              => 'Req.',
    'used_by_models'   		    => 'Isetshenziswe ngamamodeli',
    'order'   		            => 'I-oda',
    'create_fieldset'         => 'Fieldset entsha',
    'update_fieldset'         => 'Update Fieldset',
    'fieldset_does_not_exist'   => 'Fieldset :id does not exist',
    'fieldset_updated'         => 'Fieldset updated',
    'create_fieldset_title' => 'Create a new fieldset',
    'create_field'            => 'Insimu Engokwezifiso Entsha',
    'create_field_title' => 'Create a new custom field',
    'value_encrypted'      	        => 'Inani lale nsimu libethelwe kwi-database. Abasebenzisi kuphela abasebenzisi bazokwazi ukubuka inani elimisiwe',
    'show_in_email'     => 'Include the value of this field in checkout emails sent to the user? Encrypted fields cannot be included in emails',
    'show_in_email_short' => 'Include in emails',
    'help_text' => 'Help Text',
    'help_text_description' => 'This is optional text that will appear below the form elements while editing an asset to provide context on the field.',
    'about_custom_fields_title' => 'About Custom Fields',
    'about_custom_fields_text' => 'Custom fields allow you to add arbitrary attributes to assets.',
    'add_field_to_fieldset' => 'Add Field to Fieldset',
    'make_optional' => 'Required - click to make optional',
    'make_required' => 'Optional - click to make required',
    'reorder' => 'Reorder',
    'db_field' => 'DB Field',
    'db_convert_warning' => 'WARNING. This field is in the custom fields table as <code>:db_column</code> but should be <code>:expected</code>.',
    'is_unique' => 'This value must be unique across all assets',
    'unique' => 'Unique',
    'display_in_user_view' => 'Allow the checked out user to view these values in their View Assigned Assets page',
    'display_in_user_view_table' => 'Visible to User',
    'auto_add_to_fieldsets' => 'Automatically add this to every new fieldset',
    'add_to_preexisting_fieldsets' => 'Add to any existing fieldsets',
    'show_in_listview' => 'Show in list views by default. Authorized users will still be able to show/hide via the column selector',
    'show_in_listview_short' => 'Show in lists',
    'show_in_requestable_list_short' => 'Show in requestable assets list',
    'show_in_requestable_list' => 'Show value in requestable assets list. Encrypted fields will not be shown',
    'encrypted_options' => 'This field is encrypted, so some display options will not be available.',
    'display_checkin' => 'Display in checkin forms',
    'display_checkout' => 'Display in checkout forms',
    'display_audit' => 'Display in audit forms',
    'types' => [
        'text' => 'Text Box',
        'listbox' => 'List Box',
        'textarea' => 'Textarea (multi-line)',
        'checkbox' => 'Checkbox',
        'radio' => 'Radio Buttons',
    ],
];
