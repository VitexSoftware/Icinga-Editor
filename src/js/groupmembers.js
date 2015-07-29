
function addGroupMember( oClass, oRecordId, oField, memberName, memberId ){ 
    $.post( "datasaver.php?operation=add", { SaverClass: oClass , Field: oField, Key: oRecordId, Value: memberName, ValueID: memberId } 
    , function(data, status) {
        $("#" + oClass + "_" + oField + "_" + memberId + " button").removeClass('btn-inverse').addClass('btn-success');
        var addlink = $("#" + oClass + "_" + oField + "_" + memberId + " .handle");
        $(addlink).attr("onClick", $(addlink).attr("onClick").toString().replace('addGroupMember','delGroupMember')  );
        $(addlink).html('<span class="glyphicon glyphicon-remove"></span> ' + $(addlink).attr('data-deltext') );
        
}).fail(function(err, status) {
        $("#" + oClass + "_" + oField + "_" + memberId + " button").removeClass('btn-inverse').addClass('btn-danger');
        });
}

function delGroupMember( oClass, oRecordId, oField, memberName, memberId ){ 
    $.post( "datasaver.php?operation=del", { SaverClass: oClass , Field: oField, Key: oRecordId, Value: memberName, ValueID: memberId }    
    , function(data, status) {
        $("#" + oClass + "_" + oField + "_" + memberId + " button").removeClass('btn-success').addClass('btn-inverse');
        var delLink = $("#" + oClass + "_" + oField + "_" + memberId + " .handle");
        $(delLink).attr("onClick", $(delLink).attr("onClick").toString().replace('delGroupMember','addGroupMember')  );
        $(delLink).html('<span class="glyphicon glyphicon-plus-sign"></span> ' + $(delLink).attr('data-addtext') );
        
}).fail(function(err, status) {
        $("#" + oClass + "_" + oField + "_" + memberId + " button").removeClass('btn-inverse').addClass('btn-danger');
        } );    
}




