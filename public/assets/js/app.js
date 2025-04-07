"use strict"
    $(document).ready(function () {
    $("#load-more-figures").click(function () {
        $(".js-figure.d-none").slice(0, 15).removeClass("d-none").hide().fadeIn(500);

        // Cache le bouton si toutes les figures sont visibles
        if ($(".js-figure.d-none").length === 0) {
            $(this).fadeOut();
        }
    });

        $("#toggle-medias").click(function () {
    
            $("#medias-container").toggleClass("d-none");
             $("#medias-container").toggleClass("d-md-none");
        });
});