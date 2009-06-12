<div<?php echo ($class) ? ' class="' . $class . '"' : ''?><?php echo ($id) ? ' id="' . $id . '"' : ''?>>
	<span class="pct15"><label for="<?php echo $id; ?>"><?php echo $caption; ?></label></span>
    <?php if (isset($pct) && $pct > 25) { $pct = $pct; $hpct = 0; } else { $pct = 15; $hpct = "pct65"; } ?>
        <?php
        $style .= (isset($pct)) ? 'width:100%;' : '';
        $style .= (isset($disabled)) ? 'color:#999999;' : '';
        ?>
    <span class="pct<?php echo $pct; ?>"><input type="text" name="<?php echo $field; ?>" <?php if(!empty($style)) { echo "style='$style'"; } ?> value="<?php echo $value; ?>" <?php echo isset($disabled) ? 'disabled="disabled"' : ''; ?> <?php echo isset($tabindex) ? ' tabindex="' . $tabindex . '"' : ''?>><?php if (is_string($hpct)) {?></span><?php } ?>
	<span class="<?php echo (is_string($hpct)) ? $hpct : ''; ?> helptext">
        <?php if ($message != '') { 
                echo '<p class="error">' . $message . '</p>';
            } else if (isset($helptext)) {
                echo $helptext;
            } else {
                echo "&nbsp;";
            } ?></span>
    <?php if(!is_string($hpct)) { ?></span><?php } ?>
</div>

