<div class="update_totals_on_change">

	<?php
		
		woocommerce_form_field( '_discount', array(
	        'type'          => 'number',
	        'custom_attributes' => array(
		        'step'			=> 0.01,
		        'min'			=> 0
	        ),
	        'class'         => array('form-row-wide'),
	        'label'         => __('Discount (%)'),
	        'placeholder'   => __('10%')
	        ), $discount );
	        
	?>
	
</div>