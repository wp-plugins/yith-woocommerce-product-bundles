<?php

$settings = array(

	'settings'  => array(

		'general-options' => array(
			'title' => __( 'General Options', 'yith-wcpb' ),
			'type' => 'title',
			'desc' => '',
			'id' => 'yith-wcpb-general-options'
		),

		'general-options-end' => array(
			'type'      => 'sectionend',
			'id'        => 'yith-wcqv-general-options'
		)

	)
);

return apply_filters( 'yith_wcpb_panel_settings_options', $settings );