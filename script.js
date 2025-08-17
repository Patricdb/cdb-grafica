jQuery(document).ready(function ($) {
  $('.accordion-toggle').on('click', function () {
    const accordion = $(this).closest('.accordion');
    const header    = accordion.find('.accordion-header').first();
    const content   = accordion.find('.accordion-content').first();

    content.slideToggle(300);
    $(this).toggleClass('open');
    header.toggleClass('open');

    // Si quieres cerrar los demás, descomenta:
    // $('.accordion').not(accordion).each(function(){
    //   $(this).find('.accordion-content').slideUp(300);
    //   $(this).find('.accordion-toggle').removeClass('open');
    //   $(this).find('.accordion-header').removeClass('open');
    // });
  });
});
