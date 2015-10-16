$(document).on('ready', function() {
    $('.rating-proxy').on('click', function() {
        var input = $('input[' + $(this).data('input') + ']');
        var rating = $(this).data('rating');
        input.val(rating);
        $(this).siblings('.rating').width(rating * 10 + '%');
    });
});