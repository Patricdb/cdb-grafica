jQuery(document).ready(function ($) {
  $('.accordion-toggle').on('click', function () {
    const header = $(this).closest('.accordion-header');
    const accordion = header.closest('.accordion');
    const content = accordion.find('> .accordion-content');

    content.slideToggle(300);
    $(this).toggleClass('open');
    header.toggleClass('open');   // 👈 añade/quita .open en el header
  });
});
