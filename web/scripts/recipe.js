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

    $('#recipe-list th').on('click', function() {
        if ($(this).hasClass('active')) {
            $(this).toggleClass('desc');
        }
        else {
            $('#recipe-list th').removeClass('.active');
            $(this).addClass('active');
        }

        var $recipes = $('.recipe');
        $recipes.remove();
        var field = $(this).data('sort-field');
        if ($(this).hasClass('desc')) {
            // sort in reverse order
            $recipes.sort(function(recipe1, recipe2) {
                return $(recipe2).data(field) - $(recipe1).data(field);
            });
        }
        else {
            $recipes.sort(function(recipe1, recipe2) {
                return $(recipe1).data(field) - $(recipe2).data(field);
            });
        }

        $recipes.insertAfter('#recipe-list th:last-child');
    });
});