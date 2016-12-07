<?php
/**
 * Connected Communities Initiative
 * Copyright (C) 2016  Queensland University of Technology
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
?>
<?php $form=$this->beginWidget('CActiveForm', array(
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>
	        		
	<?php // echo $form->error($model,'username'); ?>
	<?php // echo $form->labelEx($model,'username'); ?>
	<?php // echo $form->textField($model, 'username', array()); ?>
	<br />
	<?php echo $form->error($model,'email'); ?>
	<?php echo $form->labelEx($model,'email'); ?>
	<?php echo $form->textField($model, 'email', array('id' => 'email')); ?>
	<br />
	<?php echo $form->labelEx($model,'firstName'); ?>
	<?php echo $form->textField($model, 'firstName', array()); ?>
	<br />
	<?php echo $form->labelEx($model,'lastName'); ?>
	<?php echo $form->textField($model, 'lastName', array()); ?>
	<br />
	<?php echo $form->labelEx($model,'image'); ?>
	<?php echo $form->textArea($model, 'image', array('id' => 'image', 'class' => 'form-control', 'rows' => '8')); ?>
	<?php echo CHtml::submitButton('Submit', array('class' => ' btn btn-info pull-right', 'style' => 'margin-top: 5px;')); ?>


<?php $this->endWidget(); ?>



<canvas id="identicon" width="100" height="100" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/jdenticon/1.3.2/jdenticon.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.3.0/js/md5.min.js"></script>

<script>
	$(function() {
		
		// Update the jdenticon canvas and dataURL input value
		function generateJdenticon(value) {
			jdenticon.update("#identicon", md5(value));
	    	$("#image").val($("#identicon").get(0).toDataURL());
		}	    

		// Listen for changes
        $( "#email" ).keypress(function() {
        	generateJdenticon($(this).val());
        });

        $( "#email" ).change(function() {
        	generateJdenticon(this.value);
        });

        // Init
        generateJdenticon("foo@bar.com");

	});
</script>