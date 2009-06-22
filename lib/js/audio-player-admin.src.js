(function ($) {
    var fieldSelector,
            colorField,
            colorPicker,
            colorSwatch,
            currentColorField,
            player;

    var init = function () {
        // Add disable behaviour to transparent checkbox
        $("input[name='cs_transparentpagebg']").click(function () {
            var bgField = $("input[name='cs_pagebg']");
            if ($("input[name='cs_transparentpagebg']").attr("checked")) {
                bgField.attr("disabled", true);
                bgField.css("color", "#999999");
            }
            else {
                bgField.attr("disabled", false);
                bgField.css("color", "#000000");
            }
        });

        // Add disable behaviour to Custom path:
        var customField = $("input[name='customPath']");
        if ($("#defaultPath select :selected").val() == 'custom') {
            customField.attr("disabled", false);
            customField.css("color", "#000000");
        } else {
            customField.attr("disabled", true);
            customField.css("color", "#999999");
        }

        $("#defaultPath select").change(function () {
            if (this.value == "custom") {
                customField.attr("disabled", false);
                customField.css("color", "#000000");
            } else {
                customField.attr("disabled", true);
                customField.css("color", "#999999");
            }
        });

        // Add disable behaviour to feedAlt
        var feedCust = $("input[name='feedCustom']");
        if ($("#feedAlt select :selected").val() == 'custom') {
            feedCust.attr("disabled", false);
            feedCust.css("color", "#000000");
        } else {
            feedCust.attr("disabled", true);
            feedCust.css("color", "#999999");
        }

        $("#feedAlt select").change(function () {
            if (this.value == "custom") {
                feedCust.attr("disabled", false);
                feedCust.css("color", "#000000");
            } else {
                feedCust.attr("disabled", true);
                feedCust.css("color", "#999999");
            }
        });

        // Reset colour scheme button
        $("#doresetcolors").click(function () {
            $("[name='resetColors']").val("1");
            $("#hbaudioplayer").submit();
        });

        // Colour scheme controls
        fieldSelector = $("#fieldsel select");
        colorField = $("#colorvalue");
        colorPicker = $("#colorsample");
        colorSwatch = $("#colorsample");
        currentColorField = $("input[name='cs_" + fieldSelector.val() + "color']");

        fieldSelector.change(function () {
            currentColorField = $("input[name='cs_" + fieldSelector.val() + "color']");
            colorField.val(currentColorField.val());
            colorPicker.ColorPickerSetColor(currentColorField.val());
            colorSwatch.css("background-color", currentColorField.val());
        });

        colorField.keyup(function () {
            var color = colorField.val();
            if (color.match(/#?[0-9a-f]{6}/i)) {
                currentColorField.val(color);
                colorSwatch.css("background-color", color);
                colorPicker.ColorPickerSetColor(currentColorField.val());
                updatePlayer();
            }
        });

        // Theme Color Picker
        var themeColorPicker = $("#themecolor");
        if (themeColorPicker) {
            themeColorPicker.css("display", "none");
            themeColorPickerBtn = $("#themecolor-btn");
            themeColorPickerBtn.click(function (evt) {
                themeColorPicker.css({
                    top : themeColorPickerBtn.position().top + themeColorPickerBtn.height() + 15,
                    left : themeColorPickerBtn.position().left
                });
                themeColorPicker.toggle();
                evt.stopPropagation();
            });
            $("li", themeColorPicker).click(function (evt) {
                var color = $(this).attr("title");
                if (color.length == 4) {
                    color = color.replace(/#(.)(.)(.)/, "#$1$1$2$2$3$3");
                }
                colorField.val(color);
                currentColorField.val(color);
                colorSwatch.css("background-color", color);
                updatePlayer();
                $("#themecolor").css("display", "none");
                evt.stopPropagation();
            });
            $(document).click(function () {
                themeColorPicker.hide();
            });
        }

        colorPicker.ColorPicker({
            onChange: function (hsb, hex, rgb) {
                var color = "#" + hex;
                colorField.val(color);
                currentColorField.val(color);
                colorSwatch.css("background-color", color);
                updatePlayer();
            },
            onShow: function () {
                themeColorPicker.hide();
            }
        });
        selectColorField();
    }

    var selectColorField = function () {
        currentColorField = $("[name='cs_" + fieldSelector.val() + "color']");
        colorField.val(currentColorField.val());
        colorPicker.ColorPickerSetColor(currentColorField.val());
        colorSwatch.css("background-color", currentColorField.val());
    }

    var updatePlayer = function () {
        player = audioplayer_swfobject.getObjectById("demoplayer");
        $("#colourselector input[type=hidden]").each(function (i) {
            player.SetVariable($(this).attr("name").replace(/cs_(.+)color/, "$1"), $(this).val().replace("#", ""));
        });
        player.SetVariable("setcolors", 1);
    }

    var pickThemeColor = function (evt) {
        var color = target.attr("title");
        if (color.length == 4) {
            color = color.replace(/#(.)(.)(.)/, "#$1$1$2$2$3$3");
        }
        $("#colorvalue").val(color);
        getCurrentColorField().val(color);
        updatePlayer();
        $("#colorsample").ColorPickerSetColor(color);
        $("colorsample").css("background-color", color);
        $("#themecolor").css("display", "none");
    }
    $(init);
})(jQuery);
