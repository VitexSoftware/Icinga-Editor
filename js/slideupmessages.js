
$(document).ready(function(){
    $('#smdrag').on('mousedown', function(e){
        $("#StatusMessages").slideDown("slow");
        $('#smdrag').css('background-color','gray');
        
        
        var $dragable = $('#StatusMessages'),
            startHeight = $dragable.height() - 10,
            pY = e.pageY;
        
        $(document).on('mouseup', function(e){
            $(document).off('mouseup').off('mousemove');
        });
        $(document).on('mousemove', function(me){
            var my = (me.pageY - pY);
            
            $dragable.css({
                height: startHeight + my
            });
        });
                
    });
    
    $('#smdrag').on('mouseout', function(e){
        $('#smdrag').css('background-color','white');
    });
    
    $("#StatusMessages").click(function () { 
            $("#StatusMessages").slideUp("slow");
            $("#StatusMessages").attr('data-state','up');
    });

});


