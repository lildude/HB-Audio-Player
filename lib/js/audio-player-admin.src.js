(function ($) {
    var timer,
            tabs,
            panels,
            fieldSelector,
            colorField,
            colorPicker,
            colorSwatch,
            currentColorField,
            player;

    var init = function () {
        // Setup tabs and panels
        /* Not used by Habari
        tabs = $("#ap_tabs li");
        panels = $("div.ap_panel");

        $("#ap_tabs li>a").click(tabClick);
        $("#ap_tabs li:first").addClass("current");
        panels.css("display", "none");
        $("div.ap_panel:first").css("display", "block");

        // Add behaviour to enclosure checkbox
        $("#ap_enclosure-at-top").attr("disabled", !$("#ap_behaviour-enclosure").attr("checked"));
        $("#ap_behaviour-enclosure").click(function () {
            $("#ap_enclosure-at-top").attr("disabled", !$(this).attr("checked"));
        });
        */
        // Add behaviour to transparent checkbox

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

        // Add behaviour to Custom path:
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

        // Verify audio folder button

        $("#ap_audiofolder-check").css("display", "block");
        $("#ap_check-button").click(checkAudioFolder);
        $("#ap_audiowebpath_iscustom").change(setAudioCheckButton);
        setAudioCheckButton();
        
        // Reset colour scheme button

        $("#ap_reset").val("");

        $("#ap_resetcolor").click(function () {
            $("#ap_reset").val("1");
            $("#ap_option-form").submit();
        });

        // Colour scheme controls

        fieldSelector = $("#fieldsel select");
        colorField = $("#colorvalue");
        //colorPicker = $("#picker-btn");
        colorPicker = $("#colorsample");
        colorSwatch = $("#colorsample");
        //currentColorField = $("#" + fieldSelector.val() + "color");
        currentColorField = $("input[name='cs_" + fieldSelector.val() + "color']");

        fieldSelector.change(function () {
            //currentColorField = $("#" + fieldSelector.val() + "color");
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

        var themeColorPicker = $("#themecolor");
        if (themeColorPicker) {
            themeColorPicker.css("display", "none");
            //reorderThemeColors();
            themeColorPickerBtn = $("#themecolor-btn");
            themeColorPickerBtn.click(function (evt) {
                themeColorPicker.css({
                    top : themeColorPickerBtn.position().top + themeColorPickerBtn.height() + 15,
                    left : themeColorPickerBtn.position().left
                });
                //themeColorPicker.show();
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

    /**
     * Handles tab clicks
     */
    /* Not used in Habari
    var tabClick = function (evt) {
        var i;
        var target = $(this);
        var tab = target.parent();

        evt.preventDefault();

        if (tab.attr("class") == "current") {
            return;
        }

        tabs.removeClass("current");
        tab.addClass("current");

        panels.css("display", "none");

        var activeTabID = target.attr("href").replace(/[^#]*#/, "");

        $("#" + activeTabID).css("display", "block");

        if (activeTabID == "ap_panel-colour") {
            timer = setTimeout(updatePlayer, 100);
        } else if (timer) {
            clearTimeout(timer);
        }
    } */

    var selectColorField = function () {
        //currentColorField = $("#ap_" + fieldSelector.val() + "color");
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

    /* var reorderThemeColors = function () {
            var swatchList = this.themeColorPicker.getElement("ul");
            var swatches = swatchList.getElements("li");
            swatches.sort(function (a, b) {
                    var colorA = new Color(a.getProperty("title"));
                    var colorB = new Color(b.getProperty("title"));
                    colorA = colorA.rgbToHsb();
                    colorB = colorB.rgbToHsb();
                    if (colorA[2] < colorB[2]) {
                            return 1;
                    }
                    if (colorA[2] > colorB[2]) {
                            return -1;
                    }
                    return 0;
            });
            swatches.each(function (swatch) {
                    swatch.injectTop(swatchList);
            });
    }  */

    var pickThemeColor = function (evt) {
        var color = target.attr("title");
        if (color.length == 4) {
            color = color.replace(/#(.)(.)(.)/, "#$1$1$2$2$3$3");
        }
        $("#colorvalue").val(color);
        getCurrentColorField().val(color);
        updatePlayer();
        //$("#picker-btn").ColorPickerSetColor(color);
        $("#colorsample").ColorPickerSetColor(color);
        $("colorsample").css("background-color", color);
        $("#themecolor").css("display", "none");
    }

    var checkAudioFolder = function () {
        showMessage("checking");

        $.post(ap_ajaxRootURL + "check-audio-folder.php", {
            audioFolder: $("#ap_audiowebpath").val()
        }, audioFolderCheckResponse);
    }

    var audioFolderCheckResponse = function (data) {
        $("#ap_checking-message").css("display", "none");
        if (data == "ok") {
            showMessage("success");
        }
        else {
            $("#ap_failure-message strong").text(data);
            showMessage("failure");
        }
    }

    var showMessage = function (message) {
        $("#ap_info-message").css("display", "none");
        $("#ap_disabled-message").css("display", "none");
        $("#ap_checking-message").css("display", "none");
        $("#ap_success-message").css("display", "none");
        $("#ap_failure-message").css("display", "none");

        if (message != "none") {
            $("#ap_" + message + "-message").css("display", "block");
        }
    }

    var setAudioCheckButton = function () {
        if ($("#ap_audiowebpath_iscustom").val() == "false") {
            $("#ap_check-button").attr("disabled", false);
            showMessage("info");
        }
        else {
            $("#ap_check-button").attr("disabled", true);
            showMessage("disabled");
        }
    }

    $(init);
})(jQuery);
