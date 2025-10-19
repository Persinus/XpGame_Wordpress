(function($){
    
$(document).ready(function () {
    // Show all content by default
    $('.tab-item').addClass('show');

    $('.tab-button').on('click', function () {
        var tab = $(this).data('tab');
        var subcategory = $(this).data('subcategory');

        $('.tab-button').removeClass('active');
        $(this).addClass('active');

        $('.tab-item').removeClass('show');

        if (tab === 'all') {
            $('.tab-item').addClass('show');
        } else if (tab) {
            // filter by main category
            $('.tab-item').each(function () {
                var tabs = $(this).data('tab').toString().split(',').map(t => t.trim());
                if (tabs.includes(tab)) {
                    $(this).addClass('show');
                }
            });
        } else if (subcategory) {
            // filter by subcategory
            $('.tab-item').each(function () {
                var subcat = $(this).data('subcategory');
                if (subcat && subcat.toLowerCase() === subcategory.toLowerCase()) {
                    $(this).addClass('show');
                }
            });
        }
    });
});


})(jQuery);