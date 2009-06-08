<div<?php echo ($class) ? ' class="' . $class . '"' : ''?><?php echo ($id) ? ' id="' . $id . '"' : ''?>>
	<span class="pct15"><label for="<?php echo $field ?>"><?php echo $this->caption; ?></label></span>
	<span class="pct15" style="text-align: left;"><input type="checkbox" name="<?php echo $field; ?>" value="1" <?php echo $value ? 'checked' : ''; ?> <?php echo isset($tabindex) ? ' tabindex="' . $tabindex . '"' : ''?>></span>
	<?php if(!empty($helptext)) : ?>
	<span class="pct65 helptext"><?php echo $helptext; ?></span>
	<?php endif; ?>
	<input type="hidden" name="<?php echo $field; ?>_submitted" value="1" >
	<?php if($message != '') : ?>
	<p class="error"><?php echo $message; ?></p>
	<?php endif; ?>
</div>