<div<?php echo ($class) ? ' class="' . $class . '"' : ''?><?php echo ($id) ? ' id="' . $id . '"' : ''?>>
    <?php if (!empty($caption)) : ?>
	<span class="pct25"><label for="<?php echo $id; ?>"><?php echo $caption; ?></label></span>
    <?php endif; ?>
  	<span class="pct70 helptext"><?php echo $helptext; ?></span>
    <div style="clear:both;"></div>
    <span class="pct25">&nbsp;</span>
	<span class="pct70"><textarea name="<?php echo $field; ?>" <?php echo isset($tabindex) ? ' tabindex="' . $tabindex . '"' : ''; echo isset( $rows ) ? " rows=\"$rows\"" : ''; echo isset( $cols ) ? " cols=\"$cols\"" : '';?> ><?php echo $value; ?></textarea></span>
	<?php if(!empty($helptext)) : ?>
	<?php endif; ?>
	<?php if($message != '') : ?>
	<p class="error"><?php echo $message; ?></p>
	<?php endif; ?>
</div>