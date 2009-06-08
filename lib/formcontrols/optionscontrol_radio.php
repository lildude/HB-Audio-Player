<div<?php echo ($class) ? ' class="' . $class . '"' : ''?><?php echo ($id) ? ' id="' . $id . '"' : ''?>>
	<span class="pct15"><label for="<?php echo $id; ?>"><?php echo $caption; ?></label></span>
<span class="pct15" style="text-align: left;">
<?php foreach($options as $key => $text) : ?>
        <input style="vertical-align:top;" type="radio" name="<?php echo $field; ?>" value="<?php echo $key; ?>"<?php echo ( ( $value == $key ) ? ' checked' : '' ); ?>><label><?php echo $text; ?></label><br />
<?php endforeach; ?>
</span>
<?php if(!empty($helptext)) : ?>
	<span class="pct65 helptext"><?php echo $helptext; ?></span>
<?php endif; ?>
<?php if($message != '') : ?>
    <p class="error"><?php echo $message; ?></p>
<?php endif; ?>
</div>

