(function($){
    
    $(document).ready(function() {

    $(".newsblogger #spnc-missedcarousel").owlCarousel({
        navigation: true, // Show next and prev buttons        
        autoplay: true,
        autoplayTimeout: 1500,
        autoplayHoverPause: true,
        smartSpeed: 1300,

        //loop: false, // loop is true up to 1199px screen.
        loop: newscrunch_missed_settings.loop,
        nav: true, // is true across all sizes
        margin: 50, // margin 10px till 960 breakpoint
        autoHeight: true,
        responsiveClass: true, // Optional helper class. Add 'owl-reponsive-' + 'breakpoint' class to main element.
        //items: 3,
        dots: false,
        navText: ["<i class='fa-solid fa-angle-left'></i>", "<i class='fa-solid fa-angle-right'></i>"],
        responsive: {
            200: { items: 1 },
            480: { items: 1 },
            768: { items: 3 },
            1000: { items: 4 }
        }
    });
        }); 
})(jQuery); 