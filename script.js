jQuery(document).ready(function ($) {
    $('.accordion-toggle').on('click', function () {
        const accordion = $(this).closest('.accordion');
        const content = accordion.find('.accordion-content');

        // Alternar la visibilidad del contenido
        content.slideToggle(300); // Animación de 300ms

        // Cambiar clase para estilos de encabezado abierto/cerrado
        $(this).toggleClass('open');

        // Opcional: cerrar otros acordeones abiertos
        // $('.accordion').not(accordion).find('.accordion-content').slideUp(300);
        // $('.accordion').not(accordion).find('.accordion-toggle').removeClass('open');
    });

    $('.cdb-grafica-scores .group-toggle').on('click', function(){
        const section = $(this).closest('tbody');
        section.toggleClass('is-open');
    });
});
