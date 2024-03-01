/**
 * Created by 71934 on 2017-02-25.
 */
(function ($) {
    $.fn.extend({
        "goSelectInput": function (parms) {

            var s = [];
            var hexDigits = "0123456789abcdef";
            for (var i = 0; i < 36; i++) {
                s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
            }
            s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
            s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
            s[8] = s[13] = s[18] = s[23] = "-";
            var uuid = s.join("");

            //原版select选择框
            var $select = this;
            $select.css("display","none");

            var conf = {
                width: ((!parms.width) ? parseInt($select.css("width").replace("px","")) : parms.width),
                height: ((!parms.height) ? parseInt($select.css("height").replace("px","")) : parms.height)
            };
            // alert(conf.width + "--" + conf.height);

            //开始加载代码
            var beforeHtml = '<div class="_htools-select" style="height: ' + conf.height + 'px;line-height: ' + conf.height + 'px;width: ' + conf.width + 'px;" onclick="htools.select.doSelect(\'' + uuid + '\');">';
            beforeHtml += '<div id="_select-input_' + uuid + '" class="_select-input" style="height: ' + conf.height + 'px; width: ' + (conf.width - 30) + 'px;">';
            beforeHtml += (($select.find("option:selected").html() == '' || !$select.find("option:selected").html()) ? $select.find("option:first").html() : $select.find("option:selected").html());
            beforeHtml += '</div>' +
                '<div class="_select-selectbtn"></div>';

            beforeHtml += '<ul id="' + uuid + '" class="_select-select-ul" style="width: ' + conf.width + 'px;">';

            var $options = $select.find('option');
            $options.each(function(index){
                if($options[index].selected){
                    beforeHtml += '<li ind="' + $options[index].value + '" class="_select-li-selected">' + $options[index].innerHTML + '</li>';
                }else{
                    beforeHtml += '<li ind="' + $options[index].value + '" >' + $options[index].innerHTML + '</li>';
                }
            });
            beforeHtml += '</ul>';
            // alert(beforeHtml);

            $select.before(beforeHtml);
            $select.after("</div>");

            //轮廓盒子
            var $contentBox = $select.parent();
            //文字显示盒子
            var $input = $contentBox.find("._select-input");
            //下拉列表
            var $ul = $contentBox.find("._select-select-ul");

            $contentBox.mouseleave(function(){
                $ul.css("display", "none");
            });

            //下拉列表单元
            var $li = $ul.find("li");
            $li.click(function(){
                $("#_select-input_" + uuid).html($(this).text());
                $select.val($(this).attr("ind"));
                $li.removeClass("_select-li-selected");
                $(this).addClass("_select-li-selected");
            });


            // $contentBox[0].onclick = function(){
            //     alert("aaa");
            //     if($ul.css("display") == "none"){
            //         $ul.css("display", "block");
            //     }else{
            //         $ul.css("display", "none");
            //     }
            // };
            //
            // $contentBox.click(function(){
            //
            // });
        }
    });
})(jQuery);
var htools = {
    select: {
        doSelect: function(uuid){
            var $ul = $("#" + uuid);
            if($ul.css("display") == "none"){
                $ul.css("display", "block");
            }else{
                $ul.css("display", "none");
            }
        }

    }
};