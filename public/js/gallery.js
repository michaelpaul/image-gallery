$(function() {
    $(document).on('click', '.js-show-image', function (event) {
        var button = $(this);
        var modal = $('#showModal');

        $.getJSON(button.data('url'))
            .done(function(image) {
                var title = image.title || 'View image';
                modal.find('.modal-title').text(title);
                modal.find('.modal-body img').attr({
                    'src': image.src,
                    'alt': title
                });
                modal.modal('show');
            })
            .fail(function(jqxhr, textStatus, error) {
                if (console && console.log) {
                    console.log("Request Failed: " + textStatus + ", " + error);
                }
            });
    });
});
