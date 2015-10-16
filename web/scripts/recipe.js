$(document).on('ready', function() {
    $('.rating-proxy').on('click', function() {
        var input = $('input[name="' + $(this).data('input') + '"]');
        var rating = $(this).data('rating');
        input.val(rating);
        $(this).siblings('.rating').width(rating * 20 + '%');
    });

    $('.rating-proxy').hover(function() {
        var rating = $(this).data('rating');
        $(this).siblings('.rating').width(rating * 20 + '%');
    }, function() {
        var input = $('input[name="' + $(this).data('input') + '"]');
        var rating = input.val();
        $(this).siblings('.rating').width(rating * 20 + '%');
    });
});