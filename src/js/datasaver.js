function saveColumnData(saverClass, key, field) {
    var input = $("[name='" + field + "']");

    $.post('datasaver.php', {
        SaverClass: saverClass,
        Field: field,
        Value: $("[name='" + field + "']").val(),
        Key: key,
        success: function () {
            input.css({borderColor: "#0f0", borderStyle: "solid"}).animate({borderWidth: '5px'}, 'slow', 'linear');
            input.animate({borderColor: 'gray', borderWidth: '1px'});
        }
    }
    ).fail(function () {
            input.css({borderColor: "#f00", borderStyle: "solid"}).animate({borderWidth: '5px'}, 'slow', 'linear');
            input.animate({borderColor: 'gray', borderWidth: '1px'});
    });
}
;

