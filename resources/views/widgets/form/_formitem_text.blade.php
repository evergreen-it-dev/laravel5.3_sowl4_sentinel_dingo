<?php
if(! isset($value)) $value = null;
$error_class = $errors->has($name) ? ' has-error' : '';
?>
<div class="input-group">
    <span class="input-group-addon"><i class="fa {!! $fa_icon_class !!}"></i></span>
    {!! Form::text($name, $value, array('placeholder' =>  $placeholder, 'class' => 'form-control' . $error_class )) !!}
</div>
<span class="help-block">{!! $errors->first($name) !!}</span>
