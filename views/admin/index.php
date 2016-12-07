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
<?php
/* @var $this AnonAccountsSettingsController */
/* @var $model AnonAccountsSettings */
/* @var $form CActiveForm */
?>
<div class="panel panel-default">
    <div class="panel-heading"><strong>Anon</strong> Accounts</div>
    <div class="panel-body">

        <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'anon-accounts-settings-index2-form',
            // Please note: When you enable ajax validation, make sure the corresponding
            // controller action is handling ajax validation correctly.
            // See class documentation of CActiveForm for details on this,
            // you need to use the performAjaxValidation()-method described there.
            'enableAjaxValidation'=>false,
        )); ?>

            <div class="form-group">
                <p>Users will recieve a randomly suggested name made of a random first name and last name. </p>
                <ul>
                    <li>Options are separated by new lines.</li>
                    <li>For best results, use adjectives for first name options</li>
                </ul>
            </div>
            
            <div class="form-group">
                <!-- show flash message after saving -->
                <?php $this->widget('application.widgets.DataSavedWidget'); ?>
                <?php echo $form->errorSummary($model); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'anonAccountsFirstNameOptions'); ?>
                <?php echo $form->textArea($model, 'anonAccountsFirstNameOptions', array('class' => 'form-control', 'rows' => '8')); ?>
                <?php echo $form->error($model,'anonAccountsFirstNameOptions'); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'anonAccountsLastNameOptions'); ?>
                <?php echo $form->textArea($model, 'anonAccountsLastNameOptions', array('class' => 'form-control', 'rows' => '8')); ?>
                <?php echo $form->error($model,'anonAccountsLastNameOptions'); ?>
            </div>

            <hr>
            
            <?php echo CHtml::submitButton('Save', array('class' => 'btn btn-primary')); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>
