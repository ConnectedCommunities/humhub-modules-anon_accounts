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
/**
 * Create account page, after the user clicked the email validation link.
 */
$this->pageTitle = Yii::t('UserModule.views_auth_createAccount', '<strong>Account</strong> registration');
?>

<div class="container" style="text-align: center;">
    <h1 id="app-title" class="animated fadeIn"><?php echo CHtml::encode(Yii::app()->name); ?></h1>
    <br/>
    <div class="row">
        <div id="create-account-form" class="panel panel-default animated bounceIn" style="max-width: 500px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.views_auth_createAccount', '<strong>Account</strong> registration'); ?></div>
            <div class="panel-body">
                <fieldset>
                    <legend>Account</legend>

                    <div class="row">
                        <div class="col-md-12 media" style="margin: 0 auto;">
                            <div href="#" class="pull-left profile-size-md">
                                <?php // echo CHtml::hiddenField($identiconForm->image, 'image', array('id' => 'image')); ?>
                                <div class="media-object profile-size-md img-rounded user-image" style="position:relative;">
                                    <canvas class="" id="identicon" width="40" height="40"></canvas>
                                    <div class="profile-overlay-img profile-overlay-img-md" style="position:absolute;top:0;left:0;"></div>
                                </div>
                            </div>

                            <div class="media-body">
                                <h4 class="media-heading">Email</h4>
                                <h5><?php echo $form['User']->model->email; ?></h5>
                            </div>
                            <br />
                        </div>
                    </div>                    
                </fieldset>
                
                <?php echo $form; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/jdenticon/1.3.2/jdenticon.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.3.0/js/md5.min.js"></script>

<script type="text/javascript">
    $(function() {
        // set cursor to login field
        $('#UserPassword_newPassword').focus();

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
        generateJdenticon("<?php echo $form['User']->model->email; ?>");

    })

    // Shake panel after wrong validation
<?php foreach ($form->models as $model) : ?>
    <?php if ($model->hasErrors()) : ?>
            $('#create-account-form').removeClass('bounceIn');
            $('#create-account-form').addClass('shake');
            $('#app-title').removeClass('fadeIn');
    <?php endif; ?>
<?php endforeach; ?>

</script>
