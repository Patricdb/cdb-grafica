(function($){
    function parseRgba(val){
        var m = val.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)/);
        if(m){
            return {r:parseInt(m[1],10), g:parseInt(m[2],10), b:parseInt(m[3],10), a:m[4]?parseFloat(m[4]):1};
        }
        return null;
    }
    function hexToRgb(hex){
        hex = hex.replace('#','');
        if(hex.length===3){ hex = hex.replace(/(.)/g,'$1$1'); }
        var num = parseInt(hex,16);
        return {r:(num>>16)&255, g:(num>>8)&255, b:num&255};
    }
    $.fn.cdbColorPickerAlpha = function(opts){
        var settings = $.extend({alpha:true}, opts);
        return this.each(function(){
            var $input = $(this);
            var rgba = parseRgba($input.val()) || {r:0,g:0,b:0,a:1};
            var initColor = 'rgb('+rgba.r+','+rgba.g+','+rgba.b+')';
            $input.val(initColor);
            $input.wpColorPicker({change:update, clear:update});
            var $container = $input.closest('.wp-picker-container');
            var $holder = $container.find('.wp-picker-holder');
            var $alpha = $('<div class="cdb-alpha-slider"></div>').insertAfter($holder);
            $alpha.slider({
                min:0,max:100,step:1,value:Math.round(rgba.a*100),
                slide:function(e,ui){rgba.a=ui.value/100;update();},
                change:function(e,ui){rgba.a=ui.value/100;update();}
            });
            function update(){
                var color = $input.wpColorPicker('color');
                var rgb;
                if(color.charAt(0)==='#'){
                    rgb = hexToRgb(color);
                    color = 'rgba('+rgb.r+','+rgb.g+','+rgb.b+','+rgba.a+')';
                } else if(color.indexOf('rgb')===0){
                    color = color.replace(')',','+rgba.a+')').replace('rgb','rgba');
                }
                $input.val(color);
                $container.find('.wp-color-result').css('background-color', color);
            }
            update();
        });
    };
})(jQuery);

jQuery(function($){
    $('.cdb-color-field').cdbColorPickerAlpha({alpha:true});
});
